### Deleting Users

The deletion of users can be done via the API, using the endpoint `/users/{id}/delete`.

#### How a request would look like

The following is an example of a request made in cURL.

`curl https://api.ratelimited.me/users/11111111-1111-1111-1111-111111111111/delete -d 'email=example@example.com' -d 'password=example'`

#### How a response would look like

The following is the response that, that curl command would give.

```json
{
    "success":true
}
```

### Errors

This endpoint can error out with the following code(s): `1002`.

#### `1002`

Code `1002` means that the user id provided in {id}, and the password provided in the request body do not match with what we have in the database.