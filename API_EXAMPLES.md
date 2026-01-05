# Step by Step Module - Web Services API Examples

Tài liệu này cung cấp các ví dụ curl cho tất cả các Web Services API của module Step by Step.

## Cấu hình cơ bản

### Base URL
```
https://yourmoodlesite.com/webservice/rest/server.php
```

### Parameters chung
- `wstoken`: Token xác thực (lấy từ Site administration > Server > Web services > Manage tokens)
- `wsfunction`: Tên function cần gọi
- `moodlewsrestformat`: Format trả về (`json` hoặc `xml`)

---

## 1. CRUD Services cho Step by Step Instance

### 1.0. Tạo Step by Step Instance với nhiều Content Steps (Gộp)

**Function:** `mod_stepbystep_create_stepbystep_with_contents`

**Method:** POST

**Mô tả:** Service này cho phép tạo một stepbystep instance và nhiều content steps trong một lần gọi duy nhất, giúp tiết kiệm thời gian và đảm bảo tính nhất quán dữ liệu.

**Parameters:**
- `courseid` (int, required): ID của course
- `name` (string, required): Tên của step by step activity
- `intro` (string, optional): Mô tả giới thiệu
- `introformat` (int, optional): Format của intro (0=MOODLE, 1=HTML, 2=PLAIN, 4=MARKDOWN)
- `section` (int, optional): Section number trong course (default: 0)
- `visible` (int, optional): Hiển thị (1) hoặc ẩn (0) (default: 1)
- `visibleoncoursepage` (int, optional): Hiển thị trên course page (1) hoặc không (0) (default: 1)
- `contents` (array, optional): Mảng các content steps với cấu trúc:
  - `type` (string, required): Loại content (`text` hoặc `vocabulary`)
  - `main_title` (string, optional): Tiêu đề chính
  - `sub_heading` (string, optional): Tiêu đề phụ
  - `content_paragraphs` (string, optional): Nội dung đoạn văn (cho type `text`)
  - `term` (string, optional): Thuật ngữ (cho type `vocabulary`)
  - `phonetic` (string, optional): Phiên âm (cho type `vocabulary`)
  - `definition` (string, optional): Định nghĩa (cho type `vocabulary`)
  - `example` (string, optional): Ví dụ (cho type `vocabulary`)
  - `response_text` (string, optional): Text hiển thị trên nút (default: "Tiếp theo")

**Ví dụ curl với JSON:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -H "Content-Type: application/json" \
  -d '{
    "wstoken": "YOUR_TOKEN",
    "wsfunction": "mod_stepbystep_create_stepbystep_with_contents",
    "moodlewsrestformat": "json",
    "courseid": 2,
    "name": "Học từ vựng tiếng Anh - Bài 1",
    "intro": "<p>Đây là bài học từ vựng tiếng Anh cơ bản</p>",
    "introformat": 1,
    "section": 1,
    "visible": 1,
    "visibleoncoursepage": 1,
    "contents": [
      {
        "type": "text",
        "main_title": "Giới thiệu về từ vựng",
        "sub_heading": "Phần 1",
        "content_paragraphs": "<p>Từ vựng là nền tảng của việc học ngôn ngữ. Hãy cùng bắt đầu học từ vựng tiếng Anh cơ bản.</p>",
        "response_text": "Tiếp theo"
      },
      {
        "type": "vocabulary",
        "term": "Hello",
        "phonetic": "/həˈloʊ/",
        "definition": "Xin chào, lời chào hỏi",
        "example": "Hello, how are you? - Xin chào, bạn khỏe không?",
        "response_text": "Hợp lý!"
      },
      {
        "type": "vocabulary",
        "term": "World",
        "phonetic": "/wɜːrld/",
        "definition": "Thế giới",
        "example": "Hello World! - Xin chào thế giới!",
        "response_text": "Được rồi!"
      }
    ]
  }'
```

**Ví dụ curl với form data (cho nhiều contents):**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep_with_contents" \
  -d "moodlewsrestformat=json" \
  -d "courseid=2" \
  -d "name=Học từ vựng tiếng Anh - Bài 1" \
  -d "intro=<p>Đây là bài học từ vựng</p>" \
  -d "introformat=1" \
  -d "section=1" \
  -d "visible=1" \
  -d "visibleoncoursepage=1" \
  -d "contents[0][type]=text" \
  -d "contents[0][main_title]=Giới thiệu về từ vựng" \
  -d "contents[0][sub_heading]=Phần 1" \
  -d "contents[0][content_paragraphs]=<p>Từ vựng là nền tảng...</p>" \
  -d "contents[0][response_text]=Tiếp theo" \
  -d "contents[1][type]=vocabulary" \
  -d "contents[1][term]=Hello" \
  -d "contents[1][phonetic]=/həˈloʊ/" \
  -d "contents[1][definition]=Xin chào, lời chào hỏi" \
  -d "contents[1][example]=Hello, how are you? - Xin chào, bạn khỏe không?" \
  -d "contents[1][response_text]=Hợp lý!" \
  -d "contents[2][type]=vocabulary" \
  -d "contents[2][term]=World" \
  -d "contents[2][phonetic]=/wɜːrld/" \
  -d "contents[2][definition]=Thế giới" \
  -d "contents[2][example]=Hello World! - Xin chào thế giới!" \
  -d "contents[2][response_text]=Được rồi!"
```

**Response mẫu:**
```json
{
  "stepbystep": {
    "id": 5,
    "course": 2,
    "name": "Học từ vựng tiếng Anh - Bài 1",
    "intro": "<p>Đây là bài học từ vựng tiếng Anh cơ bản</p>",
    "introformat": 1,
    "timecreated": 1703123456,
    "timemodified": 1703123456,
    "cmid": 15
  },
  "contents": [
    {
      "id": 10,
      "stepbystep_id": 5,
      "type": "text",
      "main_title": "Giới thiệu về từ vựng",
      "sub_heading": "Phần 1",
      "content_paragraphs": "<p>Từ vựng là nền tảng của việc học ngôn ngữ. Hãy cùng bắt đầu học từ vựng tiếng Anh cơ bản.</p>",
      "term": "",
      "phonetic": "",
      "definition": "",
      "example": "",
      "response_text": "Tiếp theo",
      "storage_path": "",
      "sortorder": 1,
      "timecreated": 1703123456
    },
    {
      "id": 11,
      "stepbystep_id": 5,
      "type": "vocabulary",
      "main_title": "",
      "sub_heading": "",
      "content_paragraphs": "",
      "term": "Hello",
      "phonetic": "/həˈloʊ/",
      "definition": "Xin chào, lời chào hỏi",
      "example": "Hello, how are you? - Xin chào, bạn khỏe không?",
      "response_text": "Hợp lý!",
      "storage_path": "",
      "sortorder": 2,
      "timecreated": 1703123456
    },
    {
      "id": 12,
      "stepbystep_id": 5,
      "type": "vocabulary",
      "main_title": "",
      "sub_heading": "",
      "content_paragraphs": "",
      "term": "World",
      "phonetic": "/wɜːrld/",
      "definition": "Thế giới",
      "example": "Hello World! - Xin chào thế giới!",
      "response_text": "Được rồi!",
      "storage_path": "",
      "sortorder": 3,
      "timecreated": 1703123456
    }
  ],
  "contents_count": 3
}
```

**Lưu ý:**
- Service này tạo stepbystep instance trước, sau đó tạo từng content step theo thứ tự trong mảng
- `sortorder` sẽ được tự động gán theo thứ tự trong mảng (bắt đầu từ 1)
- Các content không hợp lệ sẽ bị bỏ qua (không throw error)
- Nếu không có contents, chỉ tạo stepbystep instance (tương tự như `create_stepbystep`)

---

### 1.1. Tạo Step by Step Instance mới

**Function:** `mod_stepbystep_create_stepbystep`

**Method:** POST

**Parameters:**
- `courseid` (int, required): ID của course
- `name` (string, required): Tên của step by step activity
- `intro` (string, optional): Mô tả giới thiệu
- `introformat` (int, optional): Format của intro (0=MOODLE, 1=HTML, 2=PLAIN, 4=MARKDOWN)
- `section` (int, optional): Section number trong course (default: 0)
- `visible` (int, optional): Hiển thị (1) hoặc ẩn (0) (default: 1)
- `visibleoncoursepage` (int, optional): Hiển thị trên course page (1) hoặc không (0) (default: 1)

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=2" \
  -d "name=Học từ vựng tiếng Anh" \
  -d "intro=<p>Đây là bài học từ vựng tiếng Anh cơ bản</p>" \
  -d "introformat=1" \
  -d "section=1" \
  -d "visible=1" \
  -d "visibleoncoursepage=1"
```

**Response mẫu:**
```json
{
  "id": 5,
  "course": 2,
  "name": "Học từ vựng tiếng Anh",
  "intro": "<p>Đây là bài học từ vựng tiếng Anh cơ bản</p>",
  "introformat": 1,
  "timecreated": 1703123456,
  "timemodified": 1703123456,
  "cmid": 15
}
```

---

### 1.2. Lấy thông tin Step by Step Instance

**Function:** `mod_stepbystep_get_stepbystep`

**Method:** POST

**Parameters:**
- `id` (int, required): ID của step by step instance

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_get_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "id=5"
```

**Response mẫu:**
```json
{
  "id": 5,
  "course": 2,
  "name": "Học từ vựng tiếng Anh",
  "intro": "<p>Đây là bài học từ vựng tiếng Anh cơ bản</p>",
  "introformat": 1,
  "timecreated": 1703123456,
  "timemodified": 1703123456,
  "cmid": 15
}
```

---

### 1.3. Cập nhật Step by Step Instance

**Function:** `mod_stepbystep_update_stepbystep`

**Method:** POST

**Parameters:**
- `id` (int, required): ID của step by step instance
- `name` (string, optional): Tên mới
- `intro` (string, optional): Mô tả mới
- `introformat` (int, optional): Format mới

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_update_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "id=5" \
  -d "name=Học từ vựng tiếng Anh - Nâng cao" \
  -d "intro=<p>Đây là bài học từ vựng tiếng Anh nâng cao</p>" \
  -d "introformat=1"
```

**Response mẫu:**
```json
{
  "id": 5,
  "course": 2,
  "name": "Học từ vựng tiếng Anh - Nâng cao",
  "intro": "<p>Đây là bài học từ vựng tiếng Anh nâng cao</p>",
  "introformat": 1,
  "timecreated": 1703123456,
  "timemodified": 1703123500,
  "cmid": 15,
  "success": true
}
```

---

### 1.4. Xóa Step by Step Instance

**Function:** `mod_stepbystep_delete_stepbystep`

**Method:** POST

**Parameters:**
- `id` (int, required): ID của step by step instance

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_delete_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "id=5"
```

**Response mẫu:**
```json
{
  "success": true,
  "message": "Step by step instance deleted successfully"
}
```

---

### 1.5. Liệt kê tất cả Step by Step Instances trong Course

**Function:** `mod_stepbystep_list_stepbystep`

**Method:** POST

**Parameters:**
- `courseid` (int, required): ID của course

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_list_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=2"
```

**Response mẫu:**
```json
[
  {
    "id": 5,
    "course": 2,
    "name": "Học từ vựng tiếng Anh",
    "intro": "<p>Đây là bài học từ vựng tiếng Anh cơ bản</p>",
    "introformat": 1,
    "timecreated": 1703123456,
    "timemodified": 1703123456,
    "cmid": 15
  },
  {
    "id": 6,
    "course": 2,
    "name": "Học ngữ pháp tiếng Anh",
    "intro": "<p>Đây là bài học ngữ pháp</p>",
    "introformat": 1,
    "timecreated": 1703123500,
    "timemodified": 1703123500,
    "cmid": 16
  }
]
```

---

## 2. CRUD Services cho Step by Step Content

### 2.1. Tạo Content Step mới

**Function:** `mod_stepbystep_create_content`

**Method:** POST

**Parameters:**
- `stepbystep_id` (int, required): ID của step by step instance
- `type` (string, required): Loại content (`text` hoặc `vocabulary`)
- `main_title` (string, optional): Tiêu đề chính
- `sub_heading` (string, optional): Tiêu đề phụ
- `content_paragraphs` (string, optional): Nội dung đoạn văn (cho type `text`)
- `term` (string, optional): Thuật ngữ (cho type `vocabulary`)
- `phonetic` (string, optional): Phiên âm (cho type `vocabulary`)
- `definition` (string, optional): Định nghĩa (cho type `vocabulary`)
- `example` (string, optional): Ví dụ (cho type `vocabulary`)
- `response_text` (string, optional): Text hiển thị trên nút (default: "Tiếp theo")
- `sortorder` (int, optional): Thứ tự sắp xếp (default: 0)

**Ví dụ 1: Tạo content step loại text**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_content" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=5" \
  -d "type=text" \
  -d "main_title=Giới thiệu về từ vựng" \
  -d "sub_heading=Phần 1" \
  -d "content_paragraphs=<p>Từ vựng là nền tảng của việc học ngôn ngữ...</p>" \
  -d "response_text=Tiếp theo" \
  -d "sortorder=1"
```

**Ví dụ 2: Tạo content step loại vocabulary**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_content" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=5" \
  -d "type=vocabulary" \
  -d "term=Hello" \
  -d "phonetic=/həˈloʊ/" \
  -d "definition=Xin chào, lời chào hỏi" \
  -d "example=Hello, how are you? - Xin chào, bạn khỏe không?" \
  -d "response_text=Hợp lý!" \
  -d "sortorder=2"
```

**Response mẫu:**
```json
{
  "id": 10,
  "stepbystep_id": 5,
  "type": "vocabulary",
  "main_title": "",
  "sub_heading": "",
  "content_paragraphs": "",
  "term": "Hello",
  "phonetic": "/həˈloʊ/",
  "definition": "Xin chào, lời chào hỏi",
  "example": "Hello, how are you? - Xin chào, bạn khỏe không?",
  "response_text": "Hợp lý!",
  "storage_path": "",
  "sortorder": 2,
  "timecreated": 1703123600
}
```

---

### 2.2. Lấy thông tin Content Step

**Function:** `mod_stepbystep_get_content`

**Method:** POST

**Parameters:**
- `id` (int, required): ID của content step

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_get_content" \
  -d "moodlewsrestformat=json" \
  -d "id=10"
```

**Response mẫu:**
```json
{
  "id": 10,
  "stepbystep_id": 5,
  "type": "vocabulary",
  "main_title": "",
  "sub_heading": "",
  "content_paragraphs": "",
  "term": "Hello",
  "phonetic": "/həˈloʊ/",
  "definition": "Xin chào, lời chào hỏi",
  "example": "Hello, how are you? - Xin chào, bạn khỏe không?",
  "response_text": "Hợp lý!",
  "storage_path": "",
  "sortorder": 2,
  "timecreated": 1703123600
}
```

---

### 2.3. Cập nhật Content Step

**Function:** `mod_stepbystep_update_content`

**Method:** POST

**Parameters:**
- `id` (int, required): ID của content step
- `type` (string, optional): Loại content mới
- `main_title` (string, optional): Tiêu đề chính mới
- `sub_heading` (string, optional): Tiêu đề phụ mới
- `content_paragraphs` (string, optional): Nội dung mới
- `term` (string, optional): Thuật ngữ mới
- `phonetic` (string, optional): Phiên âm mới
- `definition` (string, optional): Định nghĩa mới
- `example` (string, optional): Ví dụ mới
- `response_text` (string, optional): Text nút mới
- `sortorder` (int, optional): Thứ tự mới

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_update_content" \
  -d "moodlewsrestformat=json" \
  -d "id=10" \
  -d "term=Hello World" \
  -d "definition=Xin chào thế giới" \
  -d "example=Hello World! - Xin chào thế giới!"
```

**Response mẫu:**
```json
{
  "id": 10,
  "stepbystep_id": 5,
  "type": "vocabulary",
  "main_title": "",
  "sub_heading": "",
  "content_paragraphs": "",
  "term": "Hello World",
  "phonetic": "/həˈloʊ/",
  "definition": "Xin chào thế giới",
  "example": "Hello World! - Xin chào thế giới!",
  "response_text": "Hợp lý!",
  "storage_path": "",
  "sortorder": 2,
  "timecreated": 1703123600,
  "success": true
}
```

---

### 2.4. Xóa Content Step

**Function:** `mod_stepbystep_delete_content`

**Method:** POST

**Parameters:**
- `id` (int, required): ID của content step

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_delete_content" \
  -d "moodlewsrestformat=json" \
  -d "id=10"
```

**Response mẫu:**
```json
{
  "success": true,
  "message": "Content step deleted successfully"
}
```

---

### 2.5. Liệt kê tất cả Content Steps của một Step by Step Instance

**Function:** `mod_stepbystep_list_contents`

**Method:** POST

**Parameters:**
- `stepbystep_id` (int, required): ID của step by step instance

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_list_contents" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=5"
```

**Response mẫu:**
```json
[
  {
    "id": 9,
    "stepbystep_id": 5,
    "type": "text",
    "main_title": "Giới thiệu về từ vựng",
    "sub_heading": "Phần 1",
    "content_paragraphs": "<p>Từ vựng là nền tảng của việc học ngôn ngữ...</p>",
    "term": "",
    "phonetic": "",
    "definition": "",
    "example": "",
    "response_text": "Tiếp theo",
    "storage_path": "",
    "sortorder": 1,
    "timecreated": 1703123550
  },
  {
    "id": 10,
    "stepbystep_id": 5,
    "type": "vocabulary",
    "main_title": "",
    "sub_heading": "",
    "content_paragraphs": "",
    "term": "Hello",
    "phonetic": "/həˈloʊ/",
    "definition": "Xin chào, lời chào hỏi",
    "example": "Hello, how are you? - Xin chào, bạn khỏe không?",
    "response_text": "Hợp lý!",
    "storage_path": "",
    "sortorder": 2,
    "timecreated": 1703123600
  }
]
```

---

## 3. Service khác

### 3.1. Handle Completion

**Function:** `mod_stepbystep_handle_completion`

**Method:** POST

**Parameters:**
- `cmid` (int, required): Course module ID

**Ví dụ curl:**
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_handle_completion" \
  -d "moodlewsrestformat=json" \
  -d "cmid=15"
```

**Response mẫu:**
```json
{
  "completion_enabled": true,
  "completion_type": "manual",
  "success": true,
  "message": "Activity marked as completed manually"
}
```

---

## Lưu ý quan trọng

### 1. Authentication Token
- Token phải được tạo từ Site administration > Server > Web services > Manage tokens
- User tạo token phải có quyền tương ứng:
  - `mod/stepbystep:view` - Để xem và list
  - `mod/stepbystep:addinstance` - Để create, update, delete

### 2. Format Parameters
- Sử dụng `moodlewsrestformat=json` để nhận response dạng JSON
- Có thể dùng `moodlewsrestformat=xml` để nhận response dạng XML

### 3. Error Handling
Khi có lỗi, response sẽ có dạng:
```json
{
  "exception": "moodle_exception",
  "errorcode": "invalidparameter",
  "message": "Invalid parameter value detected"
}
```

### 4. URL Encoding
Khi gửi các giá trị đặc biệt hoặc HTML, cần URL encode:
```bash
# Ví dụ với HTML content
-d "intro=%3Cp%3EHello%20World%3C%2Fp%3E"
```

### 5. Testing với Postman
Bạn có thể import các request này vào Postman:
- Method: POST
- URL: `https://yourmoodlesite.com/webservice/rest/server.php`
- Body: x-www-form-urlencoded
- Parameters: Thêm các parameters như trong ví dụ curl

---

## Workflow ví dụ: Tạo Step by Step hoàn chỉnh

### Cách 1: Sử dụng service gộp (Khuyến nghị)

**Sử dụng `create_stepbystep_with_contents` để tạo tất cả trong một lần gọi:**

```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -H "Content-Type: application/json" \
  -d '{
    "wstoken": "YOUR_TOKEN",
    "wsfunction": "mod_stepbystep_create_stepbystep_with_contents",
    "moodlewsrestformat": "json",
    "courseid": 2,
    "name": "Học từ vựng tiếng Anh",
    "intro": "<p>Bài học từ vựng</p>",
    "introformat": 1,
    "section": 1,
    "contents": [
      {
        "type": "text",
        "main_title": "Giới thiệu",
        "content_paragraphs": "<p>Nội dung giới thiệu</p>",
        "response_text": "Tiếp theo"
      },
      {
        "type": "vocabulary",
        "term": "Hello",
        "phonetic": "/həˈloʊ/",
        "definition": "Xin chào",
        "example": "Hello, how are you?",
        "response_text": "Hợp lý!"
      }
    ]
  }'
```

**Ưu điểm:**
- Chỉ cần một API call
- Đảm bảo tính nhất quán dữ liệu (atomic operation)
- Tự động gán sortorder
- Nhanh hơn và tiết kiệm băng thông

---

### Cách 2: Tạo từng bước (Nếu cần kiểm soát chi tiết)

### Bước 1: Tạo Step by Step Instance
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=2" \
  -d "name=Học từ vựng tiếng Anh" \
  -d "intro=<p>Bài học từ vựng</p>" \
  -d "introformat=1"
```
→ Lưu lại `id` từ response (ví dụ: `id=5`)

### Bước 2: Tạo các Content Steps
```bash
# Content step 1 - Text
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_content" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=5" \
  -d "type=text" \
  -d "main_title=Giới thiệu" \
  -d "content_paragraphs=<p>Nội dung giới thiệu</p>" \
  -d "sortorder=1"

# Content step 2 - Vocabulary
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_create_content" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=5" \
  -d "type=vocabulary" \
  -d "term=Hello" \
  -d "phonetic=/həˈloʊ/" \
  -d "definition=Xin chào" \
  -d "sortorder=2"
```

### Bước 3: Kiểm tra kết quả
```bash
curl -X POST "https://yourmoodlesite.com/webservice/rest/server.php" \
  -d "wstoken=YOUR_TOKEN" \
  -d "wsfunction=mod_stepbystep_list_contents" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=5"
```

---

## Tài liệu tham khảo

- [Moodle Web Services Documentation](https://docs.moodle.org/dev/Web_services)
- [Moodle REST API](https://docs.moodle.org/dev/Creating_a_web_service_function)
- Module Step by Step: `mod/stepbystep/`

