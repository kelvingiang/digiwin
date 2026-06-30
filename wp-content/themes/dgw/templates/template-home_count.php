<!-- [2026-06-30]: Thêm khối Thống kê con số (Animated Counter) -->
<div class="home-count-section">
    <div class="container mx-auto">
        <div class="count-grid">
            
            <!-- Item 1: Số năm kinh nghiệm -->
            <div class="count-item">
                <div class="count-icon">
                    <!-- Icon Calendar/History -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512" fill="currentColor">
                        <path d="M152 24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H64C28.7 64 0 92.7 0 128v16 48V448c0 35.3 28.7 64 64 64H384c35.3 0 64-28.7 64-64V192 144 128c0-35.3-28.7-64-64-64H344V24c0-13.3-10.7-24-24-24s-24 10.7-24 24V64H152V24zM48 192h352v256c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V192z"/>
                    </svg>
                </div>
                <div class="count-number-wrapper">
                    <span class="count-number" data-target="43">0</span>
                    <span class="count-plus">+</span>
                </div>
                <div class="count-label"><?php _e('Years of experience', 'dgw'); ?></div>
            </div>

            <!-- Item 2: Số lượng dự án -->
            <div class="count-item">
                <div class="count-icon">
                    <!-- Icon Project/Check -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" fill="currentColor">
                        <path d="M470.6 105.4c12.5 12.5 12.5 32.8 0 45.3l-256 256c-12.5 12.5-32.8 12.5-45.3 0l-128-128c-12.5-12.5-12.5-32.8 0-45.3s32.8-12.5 45.3 0L192 338.7 425.4 105.4c12.5-12.5 32.8-12.5 45.3 0z"/>
                    </svg>
                </div>
                <div class="count-number-wrapper">
                    <span class="count-number" data-target="600">0</span>
                    <span class="count-plus">+</span>
                </div>
                <div class="count-label"><?php _e('Projects & Clients', 'dgw'); ?></div>
            </div>

            <!-- Item 3: Phương án / Giải pháp -->
            <div class="count-item">
                <div class="count-icon">
                    <!-- Icon Solution/Lightbulb -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" fill="currentColor">
                        <path d="M297.2 318.9c-15 18.4-24.1 41.8-25.1 66.8l-1.9 44.5c-.3 8.3-7.2 14.8-15.5 14.8h-115c-8.3 0-15.2-6.5-15.5-14.8l-1.9-44.5c-1-25-10.1-48.4-25.1-66.8C74.3 290.4 64 259.9 64 227.6c0-71.8 58.2-130 130-130s130 58.2 130 130c0 32.3-10.3 62.8-26.8 91.3zM194 32c-108.3 0-196 87.7-196 196c0 47.9 16.5 91.9 44 126.8c12.4 15.6 20.3 35.1 21.5 55.6l2.1 48c1.3 29.8 25.8 53.6 55.7 53.6h115c29.9 0 54.4-23.8 55.7-53.6l2.1-48c1.2-20.5 9.1-40 21.5-55.6C341.5 319.9 358 275.9 358 228C358 119.7 270.3 32 162 32h32zm-22.1 432h28.2c4.8 0 8.7-3.9 8.7-8.7c0-23.5-19.1-42.6-42.6-42.6c-4.8 0-8.7 3.9-8.7 8.7C157.5 444.9 167.1 464 171.9 464z"/>
                    </svg>
                </div>
                <div class="count-number-wrapper">
                    <span class="count-number" data-target="10">0</span>
                    <span class="count-plus">+</span>
                </div>
                <div class="count-label"><?php _e('Product Solutions', 'dgw'); ?></div>
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const counters = document.querySelectorAll('.count-number');
    const speed = 200; // Tốc độ chạy số (càng nhỏ càng nhanh)

    // Hàm đếm số
    const runCounter = (counter) => {
        const target = +counter.getAttribute('data-target');
        const count = +counter.innerText.replace(/,/g, '');
        
        // Tính bước nhảy
        const increment = target / speed;

        if (count < target) {
            // Cập nhật số với định dạng có dấu phẩy hàng nghìn
            counter.innerText = Math.ceil(count + increment).toLocaleString();
            setTimeout(() => runCounter(counter), 15);
        } else {
            // Đảm bảo số cuối cùng chính xác
            counter.innerText = target.toLocaleString();
        }
    };

    // Sử dụng Intersection Observer để chỉ chạy khi cuộn tới
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.5 // Kích hoạt khi 50% khối hiển thị trên màn hình
    };

    const counterObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Lấy tất cả các counter trong section này và chạy
                const countersInSec = entry.target.querySelectorAll('.count-number');
                countersInSec.forEach(c => runCounter(c));
                // Ngừng quan sát sau khi đã chạy (chỉ chạy 1 lần)
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    const countSection = document.querySelector('.home-count-section');
    if (countSection) {
        counterObserver.observe(countSection);
    }
});
</script>
