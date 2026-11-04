# Upgrading

Each release with upgrade steps gets a section here, newest first. Always run the migrations after
updating:

```sh
bin/console doctrine:migrations:migrate
```

Rules for changes to this plugin:

- Never edit a released migration. Add a new one.
- Keep reading `SessionGuestTokenStorage::SESSION_KEY` when changing how the guest token is stored,
  because sessions outlive deployments.
- Breaking the public API described in `CHANGELOG.md` requires a new major version.
