# mod_stepbystep — Web Service API Documentation

> **Package:** `mod_stepbystep`  
> **Version:** 1.0  
> **Last updated:** 2026-05-18

---

## Mục lục

1. [Cấu hình](#cấu-hình)
2. [Tạo stepbystep](#1-tạo-stepbystep-mod_stepbystep_create_stepbystep)
3. [Lấy thông tin stepbystep](#2-lấy-thông-tin-stepbystep-mod_stepbystep_get_stepbystep)
4. [Cập nhật stepbystep](#3-cập-nhật-stepbystep-mod_stepbystep_update_stepbystep)
5. [Xóa stepbystep](#4-xóa-stepbystep-mod_stepbystep_delete_stepbystep)
6. [Liệt kê stepbystep trong khóa học](#5-liệt-kê-stepbystep-mod_stepbystep_list_stepbystep)
7. [Liệt kê step contents](#6-liệt-kê-step-contents-mod_stepbystep_list_contents)
8. [Tạo step content đơn lẻ](#7-tạo-step-content-đơn-lẻ-mod_stepbystep_create_content)
9. [Xử lý completion](#8-xử-lý-completion-mod_stepbystep_handle_completion)
10. [Bảng tham chiếu](#bảng-tham-chiếu)

---

## Cấu hình

```bash
# Đặt biến môi trường trước khi chạy các lệnh cURL bên dưới
MOODLE_URL="https://your-moodle.com"
TOKEN="your_moodle_token_here"
WS="$MOODLE_URL/webservice/rest/server.php"
```

> **Lấy token:** Moodle → Site Administration → Plugins → Web services → Manage tokens

---

## 1. Tạo stepbystep (`mod_stepbystep_create_stepbystep`)

**Loại:** `write`  
**Capability:** `mod/stepbystep:addinstance`

### Parameters

| Tham số | Kiểu | Bắt buộc | Mặc định | Mô tả |
|---|---|---|---|---|
| `courseid` | int | ✅ | — | ID của khóa học |
| `name` | string | ✅ | — | Tên activity |
| `intro` | string | | `''` | Mô tả (HTML) |
| `introformat` | int | | `1` | Định dạng intro (1=HTML) |
| `section` | int | | `0` | Số thứ tự section |
| `visible` | int | | `1` | Hiển thị (1/0) |
| `visibleoncoursepage` | int | | `1` | Hiển thị trên trang khóa học |
| `availabilityconditionsjson` | string | | `''` | JSON điều kiện khả dụng |
| `completion` | int | | `0` | Loại completion (0=none, 1=manual, 2=auto) |
| `completionunlocked` | int | | `1` | Completion unlocked |
| `completionview` | int | | `0` | Hoàn thành khi xem (dùng khi completion=2) |
| `completionexpected` | int | | `0` | Timestamp ngày hoàn thành dự kiến |
| `showdescription` | int | | `0` | Hiển thị mô tả trên trang khóa học |
| `contents[]` | array | | `[]` | Danh sách step contents (xem bên dưới) |

#### Cấu trúc mỗi phần tử `contents[]`

| Tham số | Kiểu | Bắt buộc | Mặc định | Mô tả |
|---|---|---|---|---|
| `type` | string | ✅ | — | `text` hoặc `vocabulary` |
| `main_title` | string | | `''` | Tiêu đề chính (dùng cho type=text) |
| `sub_heading` | string | | `''` | Tiêu đề phụ (dùng cho type=text) |
| `content_paragraphs` | string | | `''` | Nội dung HTML (dùng cho type=text) |
| `term` | string | | `''` | Từ vựng (bắt buộc khi type=vocabulary) |
| `phonetic` | string | | `''` | Phiên âm (dùng cho type=vocabulary) |
| `definition` | string | | `''` | Định nghĩa (dùng cho type=vocabulary) |
| `example` | string | | `''` | Ví dụ (dùng cho type=vocabulary) |
| `response_text` | string | | `Tiếp theo` | Văn bản nút phản hồi (xem bảng tham chiếu) |
| `sortorder` | int | | `0` | Thứ tự hiển thị |

### 1a. Tạo không có step contents

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5" \
  -d "name=Bài học từ vựng số 1" \
  -d "intro=<p>Mô tả bài học</p>" \
  -d "introformat=1" \
  -d "section=0" \
  -d "visible=1" \
  -d "visibleoncoursepage=1" \
  -d "completion=2" \
  -d "completionview=1" \
  -d "completionexpected=0" \
  -d "showdescription=0"
```

### 1b. Tạo với step type = `vocabulary`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5" \
  -d "name=Vocabulary Lesson" \
  -d "intro=<p>Học từ vựng IELTS</p>" \
  -d "introformat=1" \
  -d "section=1" \
  -d "visible=1" \
  -d "completion=2" \
  -d "completionview=1" \
  -d "contents[0][type]=vocabulary" \
  -d "contents[0][term]=Accomplish" \
  -d "contents[0][phonetic]=/əˈkʌmplɪʃ/" \
  -d "contents[0][definition]=To succeed in doing something" \
  -d "contents[0][example]=She accomplished her goal." \
  -d "contents[0][response_text]=Tiếp theo" \
  -d "contents[0][sortorder]=0" \
  -d "contents[1][type]=vocabulary" \
  -d "contents[1][term]=Brilliant" \
  -d "contents[1][phonetic]=/ˈbrɪljənt/" \
  -d "contents[1][definition]=Very clever or skillful" \
  -d "contents[1][example]=A brilliant scientist." \
  -d "contents[1][response_text]=Hợp lý!" \
  -d "contents[1][sortorder]=1"
```

### 1c. Tạo với step type = `text`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5" \
  -d "name=Reading Lesson" \
  -d "intro=<p>Bài đọc hiểu</p>" \
  -d "introformat=1" \
  -d "section=2" \
  -d "visible=1" \
  -d "completion=1" \
  -d "contents[0][type]=text" \
  -d "contents[0][main_title]=Introduction" \
  -d "contents[0][sub_heading]=What is IELTS?" \
  -d "contents[0][content_paragraphs]=<p>IELTS is an international English test...</p>" \
  -d "contents[0][response_text]=Tiếp theo" \
  -d "contents[0][sortorder]=0" \
  -d "contents[1][type]=text" \
  -d "contents[1][main_title]=Section 2" \
  -d "contents[1][sub_heading]=Test Format" \
  -d "contents[1][content_paragraphs]=<p>The test has 4 parts...</p>" \
  -d "contents[1][response_text]=Được rồi!" \
  -d "contents[1][sortorder]=1"
```

### 1d. Tạo với mix type (vocabulary + text)

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5" \
  -d "name=Mixed Lesson" \
  -d "intro=" \
  -d "introformat=1" \
  -d "section=0" \
  -d "visible=1" \
  -d "completion=2" \
  -d "completionview=1" \
  -d "contents[0][type]=text" \
  -d "contents[0][main_title]=Overview" \
  -d "contents[0][content_paragraphs]=<p>Let's learn some words...</p>" \
  -d "contents[0][response_text]=Tiếp theo" \
  -d "contents[0][sortorder]=0" \
  -d "contents[1][type]=vocabulary" \
  -d "contents[1][term]=Eloquent" \
  -d "contents[1][phonetic]=/ˈɛləkwənt/" \
  -d "contents[1][definition]=Fluent or persuasive in speaking or writing" \
  -d "contents[1][example]=An eloquent speaker." \
  -d "contents[1][response_text]=Hiểu rồi!" \
  -d "contents[1][sortorder]=1"
```

### Response

```json
{
  "id": 15,
  "course": 5,
  "name": "Mixed Lesson",
  "intro": "",
  "introformat": 1,
  "timecreated": 1747567800,
  "timemodified": 1747567800,
  "cmid": 91,
  "coursemodule": 91,
  "contents_count": 2
}
```

> **`contents_count`**: Số step hợp lệ đã được lưu vào `stepbystep_content`. Step không hợp lệ (type=text mà không có title/content, hoặc type=vocabulary mà không có term) sẽ bị bỏ qua.

---

## 2. Lấy thông tin stepbystep (`mod_stepbystep_get_stepbystep`)

**Loại:** `read`  
**Capability:** `mod/stepbystep:view`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_get_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "id=15"
```

### Response

```json
{
  "id": 15,
  "course": 5,
  "name": "Mixed Lesson",
  "intro": "",
  "introformat": 1,
  "timecreated": 1747567800,
  "timemodified": 1747567800,
  "cmid": 91
}
```

---

## 3. Cập nhật stepbystep (`mod_stepbystep_update_stepbystep`)

**Loại:** `write`  
**Capability:** `mod/stepbystep:addinstance`

### Parameters

| Tham số | Kiểu | Bắt buộc | Mô tả |
|---|---|---|---|
| `cmid` | int | ✅ | Course module ID của stepbystep cần cập nhật |
| `fields[0][name]` | string | | Tên mới |
| `fields[0][intro]` | string | | Mô tả mới (HTML) |
| `fields[0][introformat]` | int | | Định dạng mô tả |
| `fields[0][section]` | int | | Di chuyển sang section khác |
| `fields[0][visible]` | int | | Ẩn/hiện (0/1) |
| `fields[0][completion]` | int | | Completion tracking (0/1/2) |
| `fields[0][completionview]` | int | | Hoàn thành khi xem |
| `fields[0][completionexpected]` | int | | Timestamp ngày hoàn thành dự kiến |
| `fields[0][showdescription]` | int | | Hiển thị mô tả |
| `fields[0][availability]` | object | | Điều kiện khả dụng |
| `fields[0][contents][]` | array | | Thay thế toàn bộ step contents (**xem lưu ý**) |

> ⚠️ **Lưu ý quan trọng về `contents`:**
> - **Không truyền `contents`** → step content **giữ nguyên** (chỉ update metadata)
> - **Truyền `contents` có dữ liệu** → **xóa hết** step cũ, insert danh sách mới
> - **Truyền `contents` rỗng** → **xóa hết** step cũ, không insert mới

### 3a. Cập nhật chỉ metadata (không đụng tới contents)

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_update_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "cmid=91" \
  -d "fields[0][name]=Tên mới của bài học" \
  -d "fields[0][intro]=<p>Mô tả mới</p>" \
  -d "fields[0][visible]=1" \
  -d "fields[0][section]=2" \
  -d "fields[0][completion]=2" \
  -d "fields[0][completionview]=1" \
  -d "fields[0][completionexpected]=0" \
  -d "fields[0][showdescription]=0"
```

### 3b. Cập nhật và thay thế toàn bộ step contents

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_update_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "cmid=91" \
  -d "fields[0][name]=Bài học đã cập nhật" \
  -d "fields[0][visible]=1" \
  -d "fields[0][completion]=2" \
  -d "fields[0][completionview]=1" \
  -d "fields[0][contents][0][type]=vocabulary" \
  -d "fields[0][contents][0][term]=Innovative" \
  -d "fields[0][contents][0][phonetic]=/ˈɪnəveɪtɪv/" \
  -d "fields[0][contents][0][definition]=Introducing new ideas" \
  -d "fields[0][contents][0][example]=An innovative approach." \
  -d "fields[0][contents][0][response_text]=Tiếp theo" \
  -d "fields[0][contents][0][sortorder]=0" \
  -d "fields[0][contents][1][type]=text" \
  -d "fields[0][contents][1][main_title]=Summary" \
  -d "fields[0][contents][1][sub_heading]=Key Points" \
  -d "fields[0][contents][1][content_paragraphs]=<p>Remember these words...</p>" \
  -d "fields[0][contents][1][response_text]=Đồng ý!" \
  -d "fields[0][contents][1][sortorder]=1"
```

### 3c. Cập nhật với Availability Conditions

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_update_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "cmid=91" \
  -d "fields[0][name]=Bài học có điều kiện" \
  -d "fields[0][completion]=2" \
  -d "fields[0][completionview]=1" \
  -d "fields[0][availability][completioncmid][0]=75" \
  -d "fields[0][availability][completioncmid][1]=76" \
  -d "fields[0][availability][timeopen]=1748000000" \
  -d "fields[0][availability][timeclose]=1749000000"
```

### 3d. Xóa toàn bộ step contents

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_update_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "cmid=91" \
  -d "fields[0][name]=Bài học trống" \
  -d "fields[0][contents][]="
```

### Response

```json
{
  "status": "success",
  "message": "Step by step updated successfully",
  "stepbystepid": 15,
  "cmid": 91,
  "contents_count": 2
}
```

---

## 4. Xóa stepbystep (`mod_stepbystep_delete_stepbystep`)

**Loại:** `write`  
**Capability:** `mod/stepbystep:addinstance`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_delete_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "id=15"
```

### Response

```json
{
  "success": true,
  "message": "Step by step instance deleted successfully"
}
```

---

## 5. Liệt kê stepbystep (`mod_stepbystep_list_stepbystep`)

**Loại:** `read`  
**Capability:** `mod/stepbystep:view`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_list_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5"
```

### Response

```json
[
  {
    "id": 15,
    "course": 5,
    "name": "Mixed Lesson",
    "intro": "",
    "introformat": 1,
    "timecreated": 1747567800,
    "timemodified": 1747567800,
    "cmid": 91
  },
  {
    "id": 12,
    "course": 5,
    "name": "Vocabulary Lesson",
    "intro": "<p>Học từ vựng IELTS</p>",
    "introformat": 1,
    "timecreated": 1747560000,
    "timemodified": 1747560000,
    "cmid": 88
  }
]
```

---

## 6. Liệt kê step contents (`mod_stepbystep_list_contents`)

**Loại:** `read`  
**Capability:** `mod/stepbystep:view`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_list_contents" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=15"
```

### Response

```json
[
  {
    "id": 31,
    "stepbystep_id": 15,
    "type": "text",
    "main_title": "Overview",
    "sub_heading": "",
    "content_paragraphs": "<p>Let's learn some words...</p>",
    "term": "",
    "phonetic": "",
    "definition": "",
    "example": "",
    "response_text": "Tiếp theo",
    "storage_path": "",
    "sortorder": 0,
    "timecreated": 1747567800
  },
  {
    "id": 32,
    "stepbystep_id": 15,
    "type": "vocabulary",
    "main_title": "",
    "sub_heading": "",
    "content_paragraphs": "",
    "term": "Eloquent",
    "phonetic": "/ˈɛləkwənt/",
    "definition": "Fluent or persuasive in speaking or writing",
    "example": "An eloquent speaker.",
    "response_text": "Hiểu rồi!",
    "storage_path": "",
    "sortorder": 1,
    "timecreated": 1747567800
  }
]
```

---

## 7. Tạo step content đơn lẻ (`mod_stepbystep_create_content`)

**Loại:** `write`  
**Capability:** `mod/stepbystep:addinstance`

> Dùng khi cần thêm 1 step vào instance đã có. Để thêm nhiều steps cùng lúc khi tạo, dùng `contents[]` trong `create_stepbystep`.

### 7a. Type = `vocabulary`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_content" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=15" \
  -d "type=vocabulary" \
  -d "term=Perseverance" \
  -d "phonetic=/ˌpɜːsɪˈvɪərəns/" \
  -d "definition=Persistence in doing something despite difficulty" \
  -d "example=Her perseverance paid off." \
  -d "response_text=Tiếp theo" \
  -d "sortorder=2"
```

### 7b. Type = `text`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_content" \
  -d "moodlewsrestformat=json" \
  -d "stepbystep_id=15" \
  -d "type=text" \
  -d "main_title=Chào mừng" \
  -d "sub_heading=Giới thiệu bài học" \
  -d "content_paragraphs=<p>Hôm nay chúng ta sẽ học...</p>" \
  -d "response_text=Tiếp theo" \
  -d "sortorder=0"
```

---

## 8. Xử lý completion (`mod_stepbystep_handle_completion`)

**Loại:** `write`  
**Capability:** `mod/stepbystep:view`

```bash
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_handle_completion" \
  -d "moodlewsrestformat=json" \
  -d "cmid=91"
```

### Response

```json
{
  "completion_enabled": true,
  "completion_type": "automatic",
  "success": true,
  "message": "Activity marked as completed automatically"
}
```

| `completion_type` | Ý nghĩa |
|---|---|
| `none` | Không bật completion |
| `manual` | Thủ công (học sinh tự đánh dấu) |
| `automatic` | Tự động khi xem |

---

## Bảng tham chiếu

### `response_text` — Giá trị hợp lệ

| Giá trị | Hiển thị |
|---|---|
| `Tiếp theo` | Tiếp theo |
| `Hợp lý!` | Hợp lý! |
| `Được rồi!` | Được rồi! |
| `Đồng ý!` | Đồng ý! |
| `Hiểu rồi!` | Hiểu rồi! |

### `completion` — Giá trị hợp lệ

| Giá trị | Ý nghĩa | Fields kèm theo |
|---|---|---|
| `0` | Không tracking | — |
| `1` | Manual | `completionexpected` (tùy chọn) |
| `2` | Automatic | `completionview=1`, `completionexpected` (tùy chọn) |

### Validation rules cho step content

| type | Hợp lệ khi |
|---|---|
| `vocabulary` | `term` không rỗng |
| `text` | `main_title` hoặc `content_paragraphs` không rỗng |

---

## Ví dụ hoàn chỉnh: Tạo bài học từ vựng + đánh dấu hoàn thành

```bash
# Bước 1: Tạo stepbystep với 3 step vocabulary
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_create_stepbystep" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5" \
  -d "name=IELTS Vocabulary Unit 1" \
  -d "intro=<p>Học 3 từ vựng cơ bản</p>" \
  -d "introformat=1" \
  -d "section=1" \
  -d "visible=1" \
  -d "completion=2" \
  -d "completionview=1" \
  -d "contents[0][type]=vocabulary" \
  -d "contents[0][term]=Abundant" \
  -d "contents[0][phonetic]=/əˈbʌndənt/" \
  -d "contents[0][definition]=More than enough" \
  -d "contents[0][example]=Abundant resources." \
  -d "contents[0][response_text]=Tiếp theo" \
  -d "contents[0][sortorder]=0" \
  -d "contents[1][type]=vocabulary" \
  -d "contents[1][term]=Concise" \
  -d "contents[1][phonetic]=/kənˈsaɪs/" \
  -d "contents[1][definition]=Giving a lot of information clearly in few words" \
  -d "contents[1][example]=A concise summary." \
  -d "contents[1][response_text]=Hợp lý!" \
  -d "contents[1][sortorder]=1" \
  -d "contents[2][type]=vocabulary" \
  -d "contents[2][term]=Diligent" \
  -d "contents[2][phonetic]=/ˈdɪlɪdʒənt/" \
  -d "contents[2][definition]=Having or showing care in one's work" \
  -d "contents[2][example]=A diligent student." \
  -d "contents[2][response_text]=Hiểu rồi!" \
  -d "contents[2][sortorder]=2"

# Bước 2: Đánh dấu hoàn thành (cmid lấy từ response bước 1)
curl -X POST "$WS" \
  -d "wstoken=$TOKEN" \
  -d "wsfunction=mod_stepbystep_handle_completion" \
  -d "moodlewsrestformat=json" \
  -d "cmid=91"
```
