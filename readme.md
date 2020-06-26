# Notes #

## Dir structue ##

```
└── docker
	└── vault
		└── config - config files
		└── data - actual stored secrets, gitignored
		└── policies - policy files, gitignored
└── src
	└── app - source code of the PHP app
```

# Install #

```
git clone git@github.com:petr-hejda/php-vault-hello-world.git
cd ./php-vault-hello-world
```

# First-use init #

1. Start Vault Docker container

```
docker-compose up -d vault
```

2. Create __unseal key__ and __root token__
```
docker-compose exec vault vault operator init -key-shares=1 -key-threshold=1
```

__Unseal keys__

Unsealing only allows accessing the vault, but you'll still need to be authenticated and authorized later on.

*Note: You can also specify to generate multiple keys `-key-shares=5` and their required treshold `-key-threshold=3`. In that case, 3 out of 5 total keys would be required to unseal the vault.*

__Root token__

The command also generates and prints out the root token.

You can later change the token if you wish.


3. Unseal the vault

```
docker-compose exec vault vault operator unseal
```

Enter the unseal key.

4. Login using root token

```
docker-compose exec vault vault login
```

Enter the root token.

5. Create policies for `user` (can read+write their own data) and `admin` (can read+write any user data).

__User__

```
nano ./docker/vault/policies/user.hcl
```

Paste this snippet:

```
path "user/{{identity.entity.metadata.username}}" {
	capabilities = ["create", "read", "update", "delete", "list"]
}

```

Save the policy from file `user.hcl` under the name `user`

```
docker-compose exec vault vault policy write user /vault/policies/user.hcl
```

__Admin__

```
nano ./docker/vault/policies/admin.hcl
```

Paste this snippet:

```
path "user/+" {
	capabilities = ["create", "read", "update", "delete", "list"]
}
```

Save the policy from file `admin.hcl` under the name `admin`

```
docker-compose exec vault vault policy write admin /vault/policies/admin.hcl
```

__Recommended__: Check that the policies are saved correctly

```
docker-compose exec vault vault policy list
docker-compose exec vault vault policy read user
docker-compose exec vault vault policy read admin
```


6. Create a policy and auser named `vaultUserManager`, so that we don't have to use `root` token to manage other users.

```
nano ./docker/vault/policies/vaultUserManager.hcl
```

Paste this snippet:

```
path "/auth/userpass/users/+" {
	capabilities = ["create", "read", "update", "delete", "list"]
}

path "/identity/entity" {
  capabilities = ["update"]
}

path "/identity/entity-alias" {
  capabilities = ["update"]
}

path "/sys/auth" {
	capabilities = ["read"]
}

```

Save the policy from file `vaultUserManager.hcl` under the name `vaultUserManager`

```
docker-compose exec vault vault policy write vaultUserManager /vault/policies/vaultUserManager.hcl
```

Enable `userPass` login and the user:

```
docker-compose exec vault vault auth enable userpass
docker-compose exec vault vault write auth/userpass/users/vaultUserManager password="vaultUserManager" policies="default,vaultUserManager"
```

7. Create a key-value secret engine for user secrets named `user`

```
docker-compose exec vault vault secrets enable -path="user" kv
```

8. Bonus: You can also unseal the vault, create policies, read+write data, and basically perform any action using UI or API.

http://127.0.0.1:8200/ui

https://www.vaultproject.io/api-docs


# Run #

1. Register `user1` with the role `user`

```
docker-compose run app php /app/register.php <username> <password> <role>
```

Example:

```
docker-compose run app php /app/register.php user1 user1 user
```

2. Create a secret for `user1`

```
docker-compose run app php /app/secret-create.php <username> <password> <secret>
```

Example:

```
docker-compose run app php /app/secret-create.php user1 user1 mySecretForUser1
```

3. Read the secret for `user1`

```
docker-compose run app php /app/secret-read-one.php <username> <password>
```

Example:

```
docker-compose run app php /app/secret-read-one.php user1 user1
```

4. Register `user2` with the role `user`

```
docker-compose run app php /app/register.php <username> <password> <role>
```

Example:

```
docker-compose run app php /app/register.php user2 user2 user
```


5. Create a secret for `user2`

```
docker-compose run app php /app/secret-create.php <username> <password> <secret>
```

Example:

```
docker-compose run app php /app/secret-create.php user2 user2 mySecretForUser2
```

6. Read the secret for `user2`

```
docker-compose run app php /app/secret-read-one.php <username> <password>
```

Example:

```
docker-compose run app php /app/secret-read-one.php user2 user2
```

7. Register `admin1` with the role `admin`

```
docker-compose run app php /app/register.php <username> <password> <role>
```

Example:

```
docker-compose run app php /app/register.php admin1 admin1 admin
```

8. Login as `admin1` and read secrets of all users

```
docker-compose run app php /app/secret-read-all.php <username> <password>
```

Example:

```
docker-compose run app php /app/secret-read-all.php admin1 admin1
```

9. Bonus: Change to `secret-read-one.php` code so it tries accessing a different user in Vault than is logged in.

DIY :-)
