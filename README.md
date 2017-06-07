# nanobox-adapter-proxmox [![Build Status Image](https://travis-ci.org/danhunsaker/nanobox-adapter-proxmox.svg)](https://travis-ci.org/danhunsaker/nanobox-adapter-proxmox)

Provider for deploying Nanobox apps to Proxmox clusters.

## Usage

This app is designed for use with Nanobox, both in development, and for
deployment of both itself and other apps.  You must have Nanobox installed
locally to use this app.  In development, `nanobox run ./dev` will start a local
server.  `nanobox deploy dry-run` will deploy a test server, to ensure
everything is working properly before final deployment, using `nanobox deploy`.

## Testing

All changes are tested through Travis-CI.

## License

The MIT License (MIT)
