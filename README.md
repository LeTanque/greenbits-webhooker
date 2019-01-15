# greenbits-webhooker
Greenbits (POS) webhook/websocket API PHP processing script
---
Greenbits is a point of sale terminal and backoffice software developer specifically for the cannabis industry. Dispensaries use Greenbits for their sales terminals and inventory systems. Greenbits syncs with the CA state-mandated track and trace program (I believe it's called Metrc). 

In October 2018, Greenbits release API specs, available at the link below.

[https://developer.greenbits.com/v1/#menu-feed](Greenbits API specs)

## Tools
This project was the first time I had ever worked with a webhook API. So, please ignore this part if you've done this work before and don't need any help setting up a dev environment. 

[https://ngrok.io](Ngrok.io tunneling tool)

[https://webhook.site](Webhook.site HTTP request inspector)

XAMPP (or equivalent) local development environment.

## Implementation
My implementation is probably different than what a lot of other (likely better) devs would do. What I did was received the webhooks with a PHP script and then organize that data into an SQL database. Then the site pulls information from the SQL database to present to users.


