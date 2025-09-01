# Step by Step Module - Full Width Interface Design

## Tổng quan

Module Step by Step đã được thiết kế lại với giao diện hiện đại và thân thiện với người dùng. Giao diện mới này có step content nằm hết chiều ngang và response text nằm bên phải với icon user.

## Đặc điểm chính

### 1. Giao diện full-width
- **Step content** nằm hết chiều ngang màn hình
- **Response text** nằm bên phải với icon user
- Layout cân đối và dễ đọc

### 2. Icon thống nhất
- Tất cả step content đều sử dụng cùng một icon (book/lesson icon)
- Response text sử dụng icon user để thể hiện phản hồi của học viên
- Icon được thiết kế với gradient đẹp mắt

### 3. Giữ nguyên logic ban đầu
- **Hiển thị step 1 và response text của step 1**
- **Click response text của step 1** sẽ hiển thị **step 2 và response text của step 2**
- **Response text của step 1 sẽ bị ẩn**
- **Nếu click response text của step cuối** sẽ gọi đến hàm **handleStepCompletion**

## Cấu trúc HTML mới

### Template (view.mustache)
```html
<div class="stepbystep-step">
    <!-- Step Content (Full width) -->
    <div class="stepbystep-step-content-full">
        <div class="stepbystep-step-header">
            <div class="stepbystep-step-icon">
                <!-- Icon thống nhất cho tất cả step -->
            </div>
            <div class="stepbystep-step-number">
                Step {{stepnumber}} of {{totalsteps}}
            </div>
        </div>
        
        <div class="stepbystep-step-content">
            <!-- Nội dung step -->
        </div>
    </div>
    
    <!-- Response Text (Right side) -->
    <div class="stepbystep-response-section">
        <div class="stepbystep-response-avatar">
            <!-- Icon user -->
        </div>
        <div class="stepbystep-response-bubble">
            <!-- Button response -->
        </div>
    </div>
</div>
```

### CSS Classes chính
- `.stepbystep-steps-container` - Container chính cho tất cả steps
- `.stepbystep-step` - Mỗi step trong hoạt động
- `.stepbystep-step-content-full` - Phần nội dung chiếm toàn bộ chiều ngang
- `.stepbystep-response-section` - Phần response bên phải
- `.stepbystep-step-icon` - Icon cho step content
- `.stepbystep-response-avatar` - Avatar cho user response

## Logic hoạt động

### 1. Khởi tạo
- Chỉ hiển thị **step 1** và **response text của step 1**
- Tất cả các step khác đều bị ẩn

### 2. Khi click response text
- **Response text của step hiện tại bị ẩn** (fadeOut)
- **Step tiếp theo được hiển thị** cùng với **response text của step đó**
- **Cuộn mượt** đến step tiếp theo

### 3. Khi đến step cuối
- Click response text sẽ gọi hàm **handleStepCompletion**
- Hiển thị **completion message**

## Cách sử dụng

### 1. Trong Moodle
- Truy cập vào hoạt động Step by Step
- Giao diện sẽ hiển thị tự động với layout full-width
- Click vào button response để chuyển sang step tiếp theo

### 2. Demo file
- Mở file `demo_chat.php` trong trình duyệt
- Xem trước giao diện full-width
- Test các chức năng cơ bản

## Responsive Design

Giao diện được thiết kế responsive:
- **Desktop**: Step content nằm hết chiều ngang, response text bên phải
- **Mobile**: Tối ưu hóa cho màn hình nhỏ
- **Tablet**: Tự động điều chỉnh theo kích thước màn hình

## Animation và Effects

- **Fade in/out**: Response text ẩn/hiện mượt mà
- **Smooth scroll**: Cuộn mượt đến step tiếp theo
- **Hover effects**: Hiệu ứng khi di chuột qua các element
- **Gradient backgrounds**: Màu sắc hiện đại và đẹp mắt

## Tùy chỉnh

### Thay đổi màu sắc
```css
.stepbystep-step-icon {
    background: linear-gradient(135deg, #YOUR_COLOR1 0%, #YOUR_COLOR2 100%);
}

.stepbystep-response-avatar {
    background: linear-gradient(135deg, #YOUR_COLOR3 0%, #YOUR_COLOR4 100%);
}
```

### Thay đổi icon
Thay thế SVG trong template:
```html
<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
    <!-- Your custom SVG path here -->
</svg>
```

## Browser Support

- **Modern browsers**: Chrome, Firefox, Safari, Edge (latest versions)
- **Mobile browsers**: iOS Safari, Chrome Mobile
- **Fallback**: Graceful degradation cho browser cũ

## Troubleshooting

### Vấn đề thường gặp
1. **CSS không load**: Kiểm tra đường dẫn file CSS
2. **JavaScript không hoạt động**: Kiểm tra console errors
3. **Layout bị vỡ**: Kiểm tra responsive breakpoints

### Debug
- Mở Developer Tools (F12)
- Kiểm tra Console tab
- Kiểm tra Network tab cho file loading

## Tương lai

Giao diện này có thể được mở rộng thêm:
- **Dark mode**: Chế độ tối
- **Custom themes**: Chủ đề tùy chỉnh
- **Advanced animations**: Hiệu ứng nâng cao
- **Accessibility**: Cải thiện khả năng truy cập

## Liên hệ

Nếu có vấn đề hoặc đề xuất cải tiến, vui lòng liên hệ với team phát triển.
