<!doctype html>
<html lang="en">
<html>
  <head>
		<meta charset="utf-8"/>
  </head>
  <body>
<?php
include 'conpdo.php';  // This is your 'connect to db file'. Plenty of templates are available which you tune to your needs. 
$pdo=OpenCon(); // Open connection to the database using $pdo, which is passed to this script from conpdo.php.  Make sure to close the connection at the end.

// Meet this condition and end. Don't meet it, continue on to processing payload
try {
	$json = file_get_contents("php://input");
	if ( $_SERVER['REQUEST_METHOD'] != "POST" ){
    header("HTTP/1.0 403 Forbidden");
    print("Forbidden");
    exit();
	}
	if ( $json == FALSE ){
		throw new Exception("Don't panic! <br>There is nothing coming in.");
	}

echo 'Heads up!';

// Assign events to the decoded json.
$events = json_decode(utf8_decode($json), true);
// All your headers are belong to me. Not really necessary. 
$headers = getallheaders();
$item_availability = $headers['X-Gb-Eventname'];
//////////////////////////////////////////////////////
// The brand table is basically a unique identifier table that also carries the brands proper name and sanitized version for the image name
// Add each distinct brand to a brand table. Don't add NULL brand. Don't duplicate entries
// Add the automatically generated buid from the table brands to product table
// Add the image column
// create the image name from the full name, lowercased, minus spaces and minus apostrophes. Could sanitize further.
// Images are pulled from a folder and the file name/db reference align with the HTML/CSS template.
$add_brand_event=$pdo->prepare(" 
	INSERT INTO brands (brand)
	SELECT DISTINCT (brand)
	AS brand
	FROM lhshooker
	WHERE brand != 'NULL'
	AND
	NOT EXISTS (SELECT brand FROM brands WHERE brands.brand = lhshooker.brand) ;
	UPDATE lhshooker 
	INNER JOIN brands
	ON lhshooker.brand = brands.brand
	SET lhshooker.buid = brands.buid;
	UPDATE brands 
	SET image=brand, image=REPLACE(image, ' ', ''), image=REPLACE(image, '''', ''), image=LOWER(image)   ");

// Insert Event PDO and binded parameters
$insert_event=$pdo->prepare("
	INSERT INTO lhshooker (id, id_product, group_id, feed_id, brand, conc_type, strain, test_results_thc, test_results_cbd, test_results_unit, name, descript, price, price_type, price_unit ) 
	VALUES (:id, :id_product, :group_id, :feed_id, :brand, :conc_type, :strain, :test_thc, :test_cbd, :test_unit, :name, :descript, :price, :price_type, :price_unit)   ");
$insert_event->bindParam(':id', $id, PDO::PARAM_STR, 75);
$insert_event->bindParam(':id_product', $id_product, PDO::PARAM_STR, 75);
$insert_event->bindParam(':group_id', $group_id, PDO::PARAM_STR, 75);
$insert_event->bindParam(':feed_id', $feed_id, PDO::PARAM_STR, 75);
$insert_event->bindParam(':brand', $brand, PDO::PARAM_STR, 99);
$insert_event->bindParam(':conc_type', $conc_type, PDO::PARAM_STR, 255);
$insert_event->bindParam(':strain', $strain, PDO::PARAM_STR, 255);
$insert_event->bindParam(':test_thc', $test_thc, PDO::PARAM_STR);
$insert_event->bindParam(':test_cbd', $test_cbd, PDO::PARAM_STR);
$insert_event->bindParam(':test_unit', $test_unit, PDO::PARAM_STR);
$insert_event->bindParam(':name', $name, PDO::PARAM_STR, 255);
$insert_event->bindParam(':descript', $descript, PDO::PARAM_STR, 255);
$insert_event->bindParam(':price', $price, PDO::PARAM_STR, 255);
$insert_event->bindParam(':price_type', $price_type, PDO::PARAM_STR, 255);
$insert_event->bindParam(':price_unit', $price_unit, PDO::PARAM_STR, 255);

// Remove event by ID PDO and binded. attempt to remove using either id or product id
$remove_id_event=$pdo->prepare("
	DELETE FROM lhshooker
	WHERE id=:id
	OR id_product=:id_product		");
$remove_id_event->bindParam(':id', $id, PDO::PARAM_STR, 99);
$remove_id_event->bindParam(':id_product', $id_product, PDO::PARAM_STR, 75);
//$remove_id_event->bindParam(':name', $name, PDO::PARAM_STR, 75);  // Removed because it was removing items with the same name in different categories.

// Removes all items with given group_id, then attempts to cross reference the menugroups table 
// and remove all entries that have a group_id NOT in the filter_group_id column
// I commented this out at some point during testing, but I didn't document why
// Definitely works without; left here for completeness.
// $remove_menugroup_event=$pdo->prepare("
// 	DELETE FROM lhshooker 
// 	WHERE group_id=:group_id;
// 	DELETE FROM lhshooker
//  	WHERE group_id NOT IN 
// 	(SELECT menugroups.filter_group_id 
// 	FROM menugroups)   	");

$remove_menugroup_event=$pdo->prepare("
	DELETE FROM lhshooker 
	WHERE group_id=:group_id AND group_id NOT IN (SELECT menugroups.filter_group_id FROM menugroups)   	");
$remove_menugroup_event->bindParam(':group_id', $group_id, PDO::PARAM_STR, 75);

// Insert menugroups into the menugroups table 
$insert_menugroups_event=$pdo->prepare("
	INSERT INTO menugroups (total_items, webhook_id, webhook_name, filter_name, filter_group_id ) 
	VALUES (:total_items, :webhook_id, :webhook_name, :filter_name, :filter_group_id)   ");
$insert_menugroups_event->bindParam(':total_items', $total_items, PDO::PARAM_INT);
$insert_menugroups_event->bindParam(':webhook_id', $id, PDO::PARAM_STR, 99);
$insert_menugroups_event->bindParam(':webhook_name', $webhook_name, PDO::PARAM_STR, 75);
$insert_menugroups_event->bindParam(':filter_name', $filter_name, PDO::PARAM_STR, 75);
$insert_menugroups_event->bindParam(':filter_group_id', $filter_group_id, PDO::PARAM_STR, 99);

// Remove rows that match the webhook_id
$remove_webhook_id_event=$pdo->prepare( "
	DELETE FROM menugroups
	WHERE webhook_id=:webhook_id
	OR (webhook_id IS NULL)	 			");
$remove_webhook_id_event->bindParam(':webhook_id', $id, PDO::PARAM_STR, 99);

//////////////////////////////////////////////////////////////////////
// Now that our statements are prepared, we loop over the incoming POST
// and break down each item entry as a db entry.
foreach ($events as $product) {
	$id = $product['id'];
	$id_product=$product['prices'][0]['product_id'];
	$group_id=$product['menu_group_id'];
	$feed_id=$product['menu_feed_id'];
	$brand = $product['brand'];
	$conc_type = $product['concentrate_type'];
	$strain = $product['strain'];
	$name = $product['name'];
	$webhook_name = $product['name'];
	$descript = $product['description'];
	$test_thc = $product['test_results_thc'];
	$test_cbd = $product['test_results_cbd'];
	$test_unit = $product['test_results_unit'];
	$prices=$product['prices'];
	$price=$product['prices'][0]['price'];
	$price_type=$product['prices'][0]['type'];
	$price_unit=$product['prices'][0]['unit'];
	
// Here, we determine the type of POST and react accordingly.
// If the POST headers say 'item_removed', we remove the item,
// if they say 'item_updated', we attempt to remove the old 
// item and then add the new one.
// If nothing fits our if statement, we end and do nothing.
	if ($item_availability == 'sync') {
		echo 'Removing all webhook id ...  ';
		$remove_webhook_id_event->execute();
		for ($i = 0; $i < count($events['menu_feed']['menu_groups']); $i++) {
			$filter_name=$product['menu_groups'][$i]['name'];
			$filter_group_id=$product['menu_groups'][$i]['id'];
			$total_items=$product['meta']['total_menu_items'];
			$insert_menugroups_event->execute();
		}
		echo 'removed old menugroups and made a new one  -  ';
		//file_put_contents('./log/sync.log',$json, FILE_APPEND );
		//echo 'logged the sync POST';
	}
	elseif ($item_availability == 'item_unavailable') {
		$remove_id_event->execute(); // attempts to remove any item by this id and then product id. 
		echo 'item unavailable';
	} 
	elseif ($item_availability == 'item_updated') {
		if ($prices == null){
			echo 'item has no price, attempted to remove entry by id  -  ';
			$remove_id_event->execute();  // attempts to remove any item by this id and then product id. 
		} else {
			echo 'removing any item with the same product id   -  ';
			$remove_id_event->execute(); // attempts to remove any item by this id and then product id. 
			echo 'inserting the event  -';
			$insert_event->execute();  // add the new updated one
			echo 'updating brands   -   ';
			$add_brand_event->execute(); // update our brands table
			echo 'item updated';
		}
	}	
	elseif ($item_availability == 'item_added') {
		echo 'removing any item that may already exist with same ID or name   -  ';
		$remove_id_event->execute(); // attempts to remove any item by this id and then product id. 
		echo 'adding item  -  ';
		$insert_event->execute();
		echo 'updating brands  -  ';
		$add_brand_event->execute(); // update our brands table		
		echo 'item added';
	} 
	elseif ($item_availability == 'item_removed') {
		echo 'removing item';
		$remove_id_event->execute();
	}	
	elseif ($item_availability == 'group_removed') {
		echo 'removing menugroup';
		$remove_menugroup_event->execute();
	}
	elseif ($item_availability == 'group_added') {
		echo 'Removing the existing group ';
		$remove_menugroup_event->execute();
		echo 'Creating the menugroup ';
		$insert_menugroups_event->execute();
	}
	elseif ($item_availability == 'group_updated') {
		echo 'Group updated with basically no information ';
	}
	else {
		echo 'nothing happened... ?';
	}
}

// end of try/catch
}
catch( Exception $e ) {
    $message = $e->getMessage();
    die( $message );
}





?>

<?php
CloseCon($pdo);
?>
  </body>
</html>


