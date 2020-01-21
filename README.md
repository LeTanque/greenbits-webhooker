# greenbits-webhooker
__Greenbits (POS) webhook/websocket API PHP processing script__

Greenbits is a point of sale terminal and backoffice software developer specifically for the cannabis industry. Dispensaries use Greenbits for their sales terminals and inventory systems. Greenbits syncs with the CA state-mandated track and trace program (I believe it's called Metrc). 

In October 2018, Greenbits release API specs, available at the link below.

[Greenbits API specs](https://developer.greenbits.com/v1/#menu-feed)


# Summary
The greenbits documentation covers so much stuff! But what I felt was missing when I wrote this was a good summary of the steps needed to build a custom website with a greenbits menu feed. 

I'm going to do a high level summary now and then come back later and build it out into a walkthrough later on. 

Custom website menu feed using greenbits webhook api overview:

* Log into the greenbits backend (needs admin)
* Go to the marketing section
* Create a “New Menu Feed”
* Select “Custom Webhook” as the menu listing
* Fill out the fields with your Store ID, Secret, URL to send the webhooks to
* For testing, the URL will be something like an Ngrok url or webhook.site
* Once your categories are configured the way you want them, switch the URL to your domain where your menu is


# Tools
This project was the first time I had ever worked with a webhook API. So, please ignore this part if you've done this work before and don't need any help setting up a dev environment. 

[Ngrok.io tunneling tool](https://ngrok.io)

[Webhook.site HTTP request inspector](https://webhook.site)

XAMPP (or equivalent) local development environment.

# Implementation
>Tl;dr: Processing webhooks into a database for the menu provided stability and reliability.

My implementation is probably different than what a lot of other devs would do. What I did was received the webhooks with a PHP script and then organize that data into an SQL database. Then the site pulls information from the SQL database to present to users.

As an update, this script has been used in production for 3 months as of today, and has functioned marvelously. Has even managed to handle some outlier situations that I didn't explicitly prepare for.

Update: 13 months the script still works great, though. I've had to flush the database and re populate from the hook two times due to network connectivity issues corrupting the webhooks, but even then, I believe the database would have corrected itself given a little time and a correct refresh hook. 

However, I know a lot more now than I did before and I'd like to redo this as a node script. Couple it all together with a PG db. Something a little easier to work with.


# Contact
If you are working on a similiar project and would like some input, help, whatever, please reach out to me. I'm glad to help!

* get (at) frank3 (dot) me

* I'm down to help and I'm also available for hire if you need someone to build the whole thing


## To do in the future
> Put together a node version of this same thing, connect to PG versus mysql. 

> Finish the walk through.


