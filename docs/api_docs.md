# API Endpoints

## 1. Parse Email (Upload File)

**Endpoint:** `POST /api/parse-email`  
**Controller:** `EmailParserController@parseEmail`

#### Description
This endpoint allows the user to upload an EML file for parsing. The server processes the email and returns structured data from the email content.

#### Request

- **Method:** `POST`
- **Headers:**
  - `Content-Type: multipart/form-data`

- **Request Body:**
  - `email_file`: (file, required) The EML file to be parsed.
  (if multiple files are uploaded it will only porcesss one)

#### Example Request

```bash
curl -X POST \
  http://your-api-domain/api/parse-email \
  -F 'email_file=@/path/to/your/file.eml'
```


## 2. Parse Email (From Path)

**Endpoint:** `GET /api/parse-email`  
**Controller:** `EmailParserController@parseEmailFromPath`

### Description
This endpoint allows the user to parse an email file located at a specified absolute path on the server.

### Request

- **Method:** `GET`
- **Query Parameters:**
  - `file_path`: (string, required) The absolute path to the EML file to be parsed.

### Example Request

```bash
curl -X GET \
  "http://your-api-domain/api/parse-email?file_path=/absolute/path/to/file.eml"