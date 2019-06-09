### Updating a Password

Updating a password can be done via the API, using the endpoint `/users/{id}/update_password`.

#### How a request would look like

The following is an example of a request made in cURL.

`curl https://api.ratelimited.me/users/11111111-1111-1111-1111-111111111111/update_password -d 'password=example' -d 'newPassword=example1'`

#### How a response would look like

The following is the response that, that curl command would give.

```json
{
    "success":true,
    "account": {
        "updated": {
            "password": true
        }
    }
}
```

### Errors

This endpoint can error out with the following code(s): 1002.

#### `1002`

Code `1002` means that the user id provided in {id}, and the (current) password given in the body do not match up in the database.
