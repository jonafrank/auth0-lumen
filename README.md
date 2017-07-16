# auth0-lumen

A Laravel Lumen service provider to manage logins through Auth0.

## Installation

Add the next lines to your composer.json

```javascript
{
    "require": {
        ...
        "jonafrank/auth0-lumen": "dev-master"
    }
    "repositories": [
        ...
        {
            "type": "vcs",
            "url": "https://github.com/jonafrank/auth0-lumen.git"
        }
    ]
}
```

Run `$ composer update`.

## Configuration

Add the next configurations to your .env file.

```bash
# Your auth0 domain. As set in the auth0 administration page
AUTH0_DOMAIN=XXXX.auth0.com

# Your APP id.  As set in the auth0 administration page
AUTH0_CLIENT_ID=XXXXX

# Your APP secret. As set in the auth0 administration page
AUTH0_CLIENT_SECRET=XXXXX

# The redirect URI.  
# Should be a route to the callback implementation.
AUTH0_REDIRECT_URI=http://<host>/auth0/callback

# Persistence Configuration
###########################
# (Boolean) Optional. Indicates if you want to persist the user info, default true
AUTH0_PERSIST_USER=true

# (Boolean) Optional. Indicates if you want to persist the access token, default false
AUTH0_PERSIST_ACCES_TOKEN=true

# (Boolean) Optional. Indicates if you want to persist the access token, default false
AUTH0_PESIST_ID_TOKEN=true

# The authorized token audiences
AUTH0_API_IDENTIFIER=

# The authorized token issuers.
# Comma delimited of issuers.
# This is used to verify the decoded tokens when using RS256
AUTH0_AUTHORIZED_ISSUERS=

# Supported algs by your API.
# Comma delimited
# Algs supported by your API
AUTH0_SUPORTED_ALGS=HS256
```

In your bootstrap/app.php file add the next line.
```php
// bootstrap/app.php
<?php
...
/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
*/
$app->register(Auth0\Lumen\Auth0ServiceProvider::class);
?>
```



And then create a view that triggers the Auth0 login.

```html
<script src="https://cdn.auth0.com/js/lock/10.16/lock.min.js"></script>
<script>
    var lock = new Auth0Lock('pHlH_Pmsb-nv3f8tPQGA8o1XLDhmEFAm', 'jonafrank.auth0.com', {
        auth: {
            redirectUrl: 'http://local.auth0.com/auth0/callback',
            responseType: 'code',
            params: {
                scope: 'openid email' // Learn about scopes: https://auth0.com/docs/scopes
            }
        }
    });
    </script>
    <button onclick="lock.show();">Login</button>
```

## Dealing with Authorization
In order to secure routes with the Auth0 login you need to use the 'auth0' middleware.

```php
<?php
// routes/web.php
...
$app->get('/secured-route', ['middleware' => 'auth0', function() use ($app) {
    ...
    dump(Auth::user()); // used to retrieve the authenticated user
}]);

?>
```
