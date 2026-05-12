# mod_checkmatepdf — Web Service API Documentation

> **Base URL:** `https://<your-moodle-domain>/webservice/rest/server.php`  
> **Format:** tất cả request dùng `moodlewsrestformat=json`  
> **Auth:** mỗi request cần kèm `wstoken=<TOKEN>` (lấy từ *Site administration → Users → Web services → Manage tokens*)

---

## Mục lục

1. [Cài đặt & kích hoạt](#1-cài-đặt--kích-hoạt)
2. [Lấy Token xác thực](#2-lấy-token-xác-thực)
3. [create_checkmatepdf](#3-create_checkmatepdf)
4. [get_checkmatepdf](#4-get_checkmatepdf)
5. [update_checkmatepdf](#5-update_checkmatepdf)
6. [delete_checkmatepdf](#6-delete_checkmatepdf)
7. [Bảng Capabilities](#7-bảng-capabilities)
8. [Mã lỗi thường gặp](#8-mã-lỗi-thường-gặp)

---

## 1. Cài đặt & kích hoạt

Sau khi đặt file vào `mod/checkmatepdf/`, chạy upgrade để Moodle đăng ký services và capabilities:

```bash
php /var/www/html/moodle/admin/cli/upgrade.php --non-interactive
```

Hoặc truy cập trình duyệt: `https://<moodle>/admin/index.php?cache=0`

Sau đó bật web service:
- **Site admin → Advanced features** → ☑ *Enable web services*
- **Site admin → Plugins → Web services → External services** → thêm token cho user

---

## 2. Lấy Token xác thực

```bash
curl -X POST "https://<moodle>/login/token.php" \
  -d "username=admin" \
  -d "password=YourPassword" \
  -d "service=moodle_mobile_app"
```

**Response:**
```json
{ "token": "abc123xyz...", "privatetoken": null }
```

---

## 3. create_checkmatepdf

Tạo mới một activity `checkmatepdf` trong course.

**Function:** `mod_checkmatepdf_create_checkmatepdf` | **Type:** `write`  
**Capability:** `mod/checkmatepdf:addinstance` (editingteacher hoặc manager)

### Parameters

| Tên | Kiểu | Bắt buộc | Mô tả |
|-----|------|----------|-------|
| `courseid` | int | ✅ | ID của course |
| `name` | string | ✅ | Tên activity |
| `intro` | string | ❌ | Mô tả (HTML), mặc định `''` |
| `introformat` | int | ❌ | Format mô tả (1=HTML), mặc định `1` |
| `section` | int | ❌ | Số thứ tự section, mặc định `0` |
| `visible` | int | ❌ | Hiển thị (1=có, 0=ẩn), mặc định `1` |
| `visibleoncoursepage` | int | ❌ | Hiện trên course page, mặc định `1` |
| `availabilityconditionsjson` | string | ❌ | JSON điều kiện mở khóa, mặc định `''` |
| `completionview` | int | ❌ | Yêu cầu xem để hoàn thành, mặc định `0` |
| `completionexpected` | int | ❌ | Timestamp hoàn thành dự kiến, mặc định `0` |
| `showdescription` | int | ❌ | Hiện mô tả trên course page, mặc định `0` |
| `url` | string | ❌ | URL file PDF (từ external storage API) |
| `file_name` | string | ❌ | Tên file gốc |
| `file_path` | string | ❌ | Đường dẫn file |

### Curl

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_create_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "courseid=5" \
  -d "name=IELTS Reading Practice" \
  -d "intro=<p>Bài luyện đọc IELTS tháng 5</p>" \
  -d "introformat=1" \
  -d "section=2" \
  -d "visible=1" \
  -d "url=https://storage.example.com/files/ielts-reading.pdf" \
  -d "file_name=ielts-reading.pdf" \
  -d "file_path=/uploads/2024/ielts-reading.pdf"
```

### Response

```json
{
  "id": 42,
  "course": 5,
  "name": "IELTS Reading Practice",
  "intro": "<p>Bài luyện đọc IELTS tháng 5</p>",
  "introformat": 1,
  "url": "https://storage.example.com/files/ielts-reading.pdf",
  "file_name": "ielts-reading.pdf",
  "file_path": "/uploads/2024/ielts-reading.pdf",
  "timemodified": 1715500000,
  "cmid": 137,
  "coursemodule": 137
}
```

---

## 4. get_checkmatepdf

Lấy thông tin chi tiết của một activity theo instance ID.

**Function:** `mod_checkmatepdf_get_checkmatepdf` | **Type:** `read`  
**Capability:** `mod/checkmatepdf:view`

### Parameters

| Tên | Kiểu | Bắt buộc | Mô tả |
|-----|------|----------|-------|
| `id` | int | ✅ | Instance ID (bảng `checkmatepdf`, không phải cmid) |

### Curl

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_get_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "id=42"
```

### Response

```json
{
  "id": 42,
  "course": 5,
  "name": "IELTS Reading Practice",
  "intro": "<p>Bài luyện đọc IELTS tháng 5</p>",
  "introformat": 1,
  "url": "https://storage.example.com/files/ielts-reading.pdf",
  "file_name": "ielts-reading.pdf",
  "file_path": "/uploads/2024/ielts-reading.pdf",
  "timemodified": 1715500000,
  "cmid": 137,
  "coursemodule": 137,
  "section": 2,
  "visible": 1,
  "visibleoncoursepage": 1,
  "availabilityconditionsjson": "",
  "completionview": 0,
  "completionexpected": 0,
  "showdescription": 0
}
```

---

## 5. update_checkmatepdf

Cập nhật activity. Dùng cấu trúc `fields[0][...]` — chỉ truyền trường nào muốn thay đổi.

**Function:** `mod_checkmatepdf_update_checkmatepdf` | **Type:** `write`  
**Capability:** `mod/checkmatepdf:addinstance`

### Parameters

| Tên | Kiểu | Mô tả |
|-----|------|-------|
| `cmid` | int ✅ | Course module ID của activity |
| `fields[0][name]` | string | Tên mới |
| `fields[0][intro]` | string | Mô tả mới |
| `fields[0][url]` | string | URL PDF mới |
| `fields[0][file_name]` | string | Tên file mới |
| `fields[0][file_path]` | string | Đường dẫn file mới |
| `fields[0][section]` | int | Di chuyển sang section khác |
| `fields[0][visible]` | int | Ẩn/hiện (`0` hoặc `1`) |
| `fields[0][completion]` | int | `0`=none, `1`=manual, `2`=auto |
| `fields[0][completionview]` | int | Yêu cầu xem (dùng khi completion=2) |
| `fields[0][completionexpected]` | int | Timestamp hoàn thành dự kiến |
| `fields[0][showdescription]` | int | Hiện mô tả trên course page |
| `fields[0][availability][completioncmid][0]` | int | cmid cần hoàn thành trước |
| `fields[0][availability][timeopen]` | int | Timestamp bắt đầu mở |
| `fields[0][availability][timeclose]` | int | Timestamp đóng |
| `fields[0][availability][gradeitemid]` | int | Grade item làm điều kiện |
| `fields[0][availability][min]` | float | Điểm tối thiểu để mở |
| `fields[0][availability][max]` | float | Điểm tối đa để mở |

### Curl — Đổi tên + URL PDF mới

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_update_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "cmid=137" \
  -d "fields[0][name]=IELTS Reading (Updated)" \
  -d "fields[0][url]=https://storage.example.com/files/ielts-reading-v2.pdf" \
  -d "fields[0][file_name]=ielts-reading-v2.pdf"
```

### Curl — Ẩn activity + bật auto-completion

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_update_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "cmid=137" \
  -d "fields[0][visible]=0" \
  -d "fields[0][completion]=2" \
  -d "fields[0][completionview]=1" \
  -d "fields[0][completionexpected]=1720000000"
```

### Curl — Yêu cầu hoàn thành activity cmid=120 trước khi mở

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_update_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "cmid=137" \
  -d "fields[0][availability][completioncmid][0]=120"
```

### Curl — Mở theo khoảng thời gian

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_update_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "cmid=137" \
  -d "fields[0][availability][timeopen]=1715500000" \
  -d "fields[0][availability][timeclose]=1718000000"
```

### Response

```json
{
  "status": "success",
  "message": "checkmatepdf updated successfully",
  "checkmatepdfid": 42,
  "cmid": 137
}
```

---

## 6. delete_checkmatepdf

Xóa vĩnh viễn một activity và toàn bộ dữ liệu liên quan.

**Function:** `mod_checkmatepdf_delete_checkmatepdf` | **Type:** `write`  
**Capability:** `mod/checkmatepdf:addinstance`

### Parameters

| Tên | Kiểu | Bắt buộc | Mô tả |
|-----|------|----------|-------|
| `id` | int | ✅ | Instance ID (bảng `checkmatepdf`) |

### Curl

```bash
curl -X POST "https://<moodle>/webservice/rest/server.php" \
  -d "wstoken=abc123xyz" \
  -d "wsfunction=mod_checkmatepdf_delete_checkmatepdf" \
  -d "moodlewsrestformat=json" \
  -d "id=42"
```

### Response

```json
{
  "success": true,
  "message": "checkmatepdf deleted successfully"
}
```

> ⚠️ **Không thể hoàn tác.** Khi xóa, Moodle sẽ xóa course_modules record, completion data và files liên quan.

---

## 7. Bảng Capabilities

| Capability | Loại | Context | Roles mặc định |
|-----------|------|---------|----------------|
| `mod/checkmatepdf:view` | read | MODULE | guest, user, student, teacher, editingteacher, manager |
| `mod/checkmatepdf:addinstance` | write | COURSE | editingteacher, manager |
| `mod/checkmatepdf:create` | write | COURSE | editingteacher, manager |
| `mod/checkmatepdf:update` | write | MODULE | editingteacher, manager |
| `mod/checkmatepdf:delete` | write | MODULE | editingteacher, manager |

---

## 8. Mã lỗi thường gặp

| Moodle Error | Nguyên nhân | Giải pháp |
|-------------|-------------|-----------|
| `invalidtoken` | Token sai hoặc hết hạn | Tạo lại token tại *Manage tokens* |
| `accessdenied` | User thiếu capability | Kiểm tra role của user trong course |
| `invalidcourseid` | `courseid` không tồn tại | Truyền đúng ID course |
| `errorcreatingcheckmatepdf` | `add_moduleinfo` thất bại | Kiểm tra module đã cài chưa, chạy lại upgrade |
| `errordeletingcheckmatepdf` | Instance không tồn tại hoặc đã bị xóa | Kiểm tra lại `id` |
| `dberror` | Lỗi database | Xem Moodle error log tại `moodledata/` |

---

## Phân biệt `id` vs `cmid`

```
course (id=5)
  └── course_modules (id=137)   ← cmid, dùng trong URL: /view.php?id=137
        └── checkmatepdf (id=42) ← instance id, dùng để get/delete
```

| Hàm | Dùng `id` | Dùng `cmid` |
|-----|-----------|-------------|
| `create` | — trả về cả hai | — trả về cả hai |
| `get` | ✅ truyền vào | — |
| `update` | — | ✅ truyền vào |
| `delete` | ✅ truyền vào | — |
