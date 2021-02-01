## Console Command Chaining
Create a Symfony bundle that implements command chaining functionality. Other Symfony bundles in the application may register their console commands to be members of a command chain. When a user runs the main command in a chain, all other commands registered in this chain should be executed as well. Commands registered as chain members can no longer be executed on their own.

Provide two other sample bundles to demonstrate the work of this application.

## Run project
```bash
docker-compose up --build -d
```

## Configuration example:
src/ChainCommandBundle/Resource/config/config.yml
````
chain_command:
  chains:
    chain_1:
      parent: 'foo:hello'
      children:
        - 'bar:hi'
    chain_2:
      parent: 'bar:hi'
      children:
        - 'foo:hello'
````

