# Step by Step Module - Form Field Visibility

## Tổng quan

Tính năng ẩn/hiển thị các element trong form tùy thuộc vào type của step đã được triển khai và cải tiến trong module Step by Step. Form hiện tại chỉ hiển thị 1 step ban đầu và có giao diện người dùng hiện đại, trực quan với animation mượt mà.

## Các loại Step Type

### 1. Vocabulary Type
Khi chọn type "Vocabulary", các field sau sẽ được hiển thị:
- **Term**: Nhập từ vựng hoặc khái niệm
- **Definition**: Nhập định nghĩa hoặc giải thích
- **Example**: Nhập ví dụ hoặc cách sử dụng
- **Audio file**: Upload file âm thanh cho phát âm
- **Response text**: Text hiển thị trên nút tiếp tục (luôn hiển thị)

### 2. Text Type
Khi chọn type "Text", các field sau sẽ được hiển thị:
- **Main title**: Tiêu đề chính cho step này
- **Sub heading**: Tiêu đề phụ hoặc chủ đề cụ thể
- **Content paragraphs**: Nội dung đoạn văn (hỗ trợ HTML)
- **Response text**: Text hiển thị trên nút tiếp tục (luôn hiển thị)

## Cách hoạt động

### Form Initialization
- Form luôn bắt đầu với **1 step duy nhất** cho activity mới
- Chỉ load nhiều steps khi đang edit activity đã tồn tại
- Mỗi step được đóng gói trong container `.fgroup` với styling riêng biệt

### JavaScript Logic
File `amd/src/form.js` chứa logic xử lý việc ẩn/hiển thị:

```javascript
function handleStepTypeChange($select) {
    var selectedType = $select.val();
    var $stepGroup = $select.closest('.fgroup');
    
    // Ẩn tất cả field trước
    $stepGroup.find('.stepbystep-text-field').closest('.fitem').addClass('stepbystep-field-hidden');
    $stepGroup.find('.stepbystep-vocabulary-field').closest('.fitem').addClass('stepbystep-field-hidden');
    
    // Hiển thị field tương ứng với type
    if (selectedType === 'vocabulary') {
        $stepGroup.find('.stepbystep-vocabulary-field').closest('.fitem').removeClass('stepbystep-field-hidden').addClass('stepbystep-field-visible');
        $stepGroup.addClass('stepbystep-type-vocabulary');
    } else if (selectedType === 'text') {
        $stepGroup.find('.stepbystep-text-field').closest('.fitem').removeClass('stepbystep-field-hidden').addClass('stepbystep-field-visible');
        $stepGroup.addClass('stepbystep-type-text');
    }
    
    // Luôn hiển thị common fields
    $stepGroup.find('.stepbystep-common-field').closest('.fitem').removeClass('stepbystep-field-hidden').addClass('stepbystep-field-visible');
}
```

### CSS Classes
Các class CSS được sử dụng:

- `.stepbystep-text-field`: Cho các field của type "text"
- `.stepbystep-vocabulary-field`: Cho các field của type "vocabulary"
- `.stepbystep-common-field`: Cho các field chung (response_text)
- `.stepbystep-field-hidden`: Ẩn field
- `.stepbystep-field-visible`: Hiển thị field với animation

## Cấu trúc Form

### Mod Form PHP
File `mod_form.php` định nghĩa các element với class CSS tương ứng:

```php
// Text fields
$repeatarray[] = $mform->createElement('text', 'main_title', get_string('main_title', 'mod_stepbystep'), 
    array('size' => 80, 'class' => 'stepbystep-text-field'));

// Vocabulary fields
$repeatarray[] = $mform->createElement('text', 'term', get_string('term', 'mod_stepbystep'), 
    array('size' => 50, 'class' => 'stepbystep-vocabulary-field'));

// Common fields
$repeatarray[] = $mform->createElement('text', 'response_text', get_string('responsetext', 'mod_stepbystep'), 
    array('size' => 50, 'class' => 'stepbystep-common-field'));
```

## Styling

### Modern UI Design
File `form.css` chứa styling hiện đại cho form:

- **Step Groups**: Mỗi step được đóng gói trong container với border, shadow và hover effects
- **Type Indicators**: Màu sắc khác nhau cho từng loại step (xanh lá cho vocabulary, vàng cho text)
- **Step Counters**: Số thứ tự hiển thị ở góc phải trên của mỗi step
- **Smooth Animations**: Transition mượt mà khi ẩn/hiển thị fields

### CSS Animation
```css
.stepbystep-field-hidden {
    display: none !important;
    opacity: 0;
    height: 0;
    overflow: hidden;
}

.stepbystep-field-visible {
    display: block !important;
    opacity: 1;
    height: auto;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
```

### Responsive Design
- Mobile-friendly với font-size 16px để tránh zoom trên iOS
- Flexible layout cho các màn hình khác nhau
- Touch-friendly buttons và controls

## Xử lý đặc biệt

### File Manager
Audio file field sử dụng Moodle's filemanager, không hỗ trợ class trực tiếp trong options. JavaScript sẽ tự động thêm class:

```javascript
// Add classes to filemanager containers
$('.filemanager').each(function() {
    var $filemanager = $(this);
    var $input = $filemanager.find('input[name^="audio_file["]');
    if ($input.length > 0) {
        $filemanager.closest('.fitem').addClass('stepbystep-vocabulary-field');
    }
});
```

## Sử dụng

### Tạo Activity Mới
1. **Tạo Step by Step activity mới**
2. **Form sẽ hiển thị 1 step duy nhất** với tất cả fields ẩn
3. **Chọn step type** để hiển thị fields tương ứng:
   - **Vocabulary**: Hiển thị term, definition, example, audio_file
   - **Text**: Hiển thị main_title, sub_heading, content_paragraphs
4. **Response text luôn hiển thị** cho cả hai type
5. **Click "Add step"** để thêm step mới khi cần

### Chỉnh Sửa Activity
1. **Edit Step by Step activity đã tồn tại**
2. **Form sẽ load tất cả steps hiện có** với fields đã được hiển thị đúng
3. **Thay đổi step type** để ẩn/hiển thị fields tương ứng
4. **Add/Remove steps** theo nhu cầu

### Visual Feedback
- **Step counter** hiển thị số thứ tự ở góc phải trên
- **Color coding**: Xanh lá cho vocabulary, vàng cho text
- **Smooth animations** khi chuyển đổi giữa các types

## Lưu ý kỹ thuật

- **Thuộc tính 'class' không được hỗ trợ trực tiếp** trong mảng options của createElement() cho một số element types
- **Filemanager** cần xử lý đặc biệt trong JavaScript
- **Animation** được sử dụng để tạo trải nghiệm mượt mà
- **Accessibility** được đảm bảo với focus styles và screen reader support

## Troubleshooting

### Field không ẩn/hiển thị
1. Kiểm tra console browser để xem JavaScript errors
2. Đảm bảo file `form.min.js` đã được build lại sau khi thay đổi
3. Kiểm tra class CSS có được áp dụng đúng không

### Audio file field không hoạt động
1. Kiểm tra JavaScript có thêm class cho filemanager container không
2. Đảm bảo context được set đúng trong filemanager options

### Performance
- Animation được giới hạn ở 0.3s để tránh lag
- CSS transitions được sử dụng thay vì JavaScript animations khi có thể

## Tương lai

Có thể mở rộng tính năng này để hỗ trợ:
- Thêm các step type mới
- Custom field visibility rules
- Conditional field validation
- Dynamic field requirements
