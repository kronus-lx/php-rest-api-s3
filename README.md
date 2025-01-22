# PHP API for AWS S3 Interaction

This PHP API is designed to seamlessly interact with AWS S3 or any S3-compatible store, using access and secret key access. It is built to run on either Windows IIS or Apache servers, with compatibility for the latest PHP version 8.3.

## Key Features

- **Compatibility**: Works with AWS S3 and any S3-compatible storage.
- **Security**: Utilizes access and secret keys for secure authentication.
- **Server Flexibility**: Can be deployed on both Windows IIS and Apache servers.
- **Modern PHP**: Designed to run with PHP version 8.3.

Configure settings by adding custom credentials.json in application folder containing S3 URI and Access Keys.

## Endpoints

### 1. List Buckets

**Endpoint**: `/buckets`

**Description**: Retrieves a list of all buckets.

### 2. List Objects in a Bucket

**Endpoint**: `/buckets/{bucket}/objects`

**Description**: Retrieves a list of all objects within the specified bucket.

### 3. Get Specific Object

**Endpoint**: `/buckets/{bucket}/object/{key}`

**Description**: Retrieves the specific object identified by the key within the specified bucket.

This API is for public usage, replication and distribution 