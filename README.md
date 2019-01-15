# greenbits-webhooker
Greenbits (POS) webhook/websocket API PHP processing script

Greenbits is a point of sale terminal and backoffice software developer specifically for the cannabis industry. Dispensaries use Greenbits for their sales terminals and inventory systems. Greenbits syncs with the CA state-mandated track and trace program (I believe it's called Metrc). 

In October 2018, Greenbits release API specs, available at the link below.

[Greenbits API specs](https://developer.greenbits.com/v1/#menu-feed)

## Tools
This project was the first time I had ever worked with a webhook API. So, please ignore this part if you've done this work before and don't need any help setting up a dev environment. 

[Ngrok.io tunneling tool](https://ngrok.io)

[Webhook.site HTTP request inspector](https://webhook.site)

XAMPP (or equivalent) local development environment.

## Implementation
My implementation is probably different than what a lot of other (likely better) devs would do. What I did was received the webhooks with a PHP script and then organize that data into an SQL database. Then the site pulls information from the SQL database to present to users.

## Contact
If you are working on a similiar project and would like some input, help, whatever, please reach out to me. I'm glad to help.

* get (at) frank3 (dot) me
