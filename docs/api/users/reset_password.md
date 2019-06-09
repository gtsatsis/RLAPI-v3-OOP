### Resetting a password

Resetting a password can be done via the API by using the endpoint `/users/reset_password`.

#### How a request would look like

The following is an example of a request made in cURL.

`curl https://api.ratelimited.me/users/reset_password -d 'email=example@example.com'`

#### How a response would look like

The following is the response that, that curl command would give.

```json
{
    "message":"if_user_exists_then_email_sent_successfully"
}
```