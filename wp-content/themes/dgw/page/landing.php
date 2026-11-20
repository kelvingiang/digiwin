<?php
declare(strict_types=1);

/**
 * Date: 2026-08-24
 * Template Name: landing Page
 * Description: Custom template for landing Page
 * [2026-11-20] - @author: Kelvin - Thay URL hardcode bằng THEME_PART/PART_IMAGES, bổ sung alt, loading="lazy", decoding="async"
 */
get_header();
get_template_part('templates/header');

$diviJsUrl  = THEME_PART . '/js/divi/landing.js?ver=1.0';
$diviCssUrl = THEME_PART . '/js/divi/landing-style.css?ver=1.0';
$diviImgDir = PART_IMAGES . 'divi/';
?>
<script src="<?php echo esc_url($diviJsUrl); ?>" defer></script>
<link rel="stylesheet" href="<?php echo esc_url($diviCssUrl); ?>">

<div class="dgw-tab-space">
    <div class="dgw-tab-title">
        <div class="dgw-title-text title-event" id-title=".title-event" id-content=".content-event">
            Giám sát tức thời
        </div>
        <div class="dgw-title-text title-news" id-title=".title-news" id-content=".content-news">
            Theo dõi nhiệm vụ
        </div>
        <div class="dgw-title-text title-article" id-title=".title-article" id-content=".content-article">
            Kanban trực quan
        </div>
        <div class="dgw-title-text title-cases" id-title=".title-cases" id-content=".content-cases">
            Tin nhắn cảnh báo
        </div>
    </div>

    <div class="dgw-tab-content">
        <div class="dgw-content content-event">
            <div class="dgw-content-list">
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Giám sát thiết bị tức thời</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Dễ dàng theo dõi trạng thái vận hành thiết bị thông qua màu sắc: chạy, dừng, lỗi, tắt</div>
                                <div> - Nhận biết ngay tổng phụ tải hiện tại và tỉ lệ sản xuất đã hoàn thành, hỗ trợ nắm bắt tình hình hoạt động trong nhà máy</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab1-1.png'); ?>" alt="Giám sát thiết bị tức thời" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Tình trạng vận hành thiết bị</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Nắm bắt phụ tải máy, đơn hàng đang được sản xuất trên thiết bị và tình trạng hoàn thành</div>
                                <div> - Theo dõi trực quan nhịp sản xuất và sản lượng thực tế</div>
                                <div> - Chủ động giám sát và kiểm soát các tham số sản xuất trên thiết bị</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab1-2.png'); ?>" alt="Tình trạng vận hành thiết bị" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Phân tích hiệu suất thiết bị</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Theo dõi trực quan hiệu suất từng thiết bị</div>
                                <div> - Kiểm soát tình trạng bất thường và tần suất xảy ra</div>
                                <div> - Dự đoán xu hướng sức khỏe của máy móc</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab1-3.png'); ?>" alt="Phân tích hiệu suất thiết bị" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dgw-content content-news">
            <div class="dgw-content-list">
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Phân tích tiến độ bằng dữ liệu</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Tra cứu dễ dàng tiến độ thực hiện theo đơn hàng/ khách hàng/ sản phẩm.</div>
                                <div> - Kiểm tra các thông tin sản xuất liên quan: người thực hiện, ngày dự kiến hoàn thành…</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab2-1.jpg'); ?>" alt="Phân tích tiến độ bằng dữ liệu" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Phân công nhiệm vụ có cơ sở</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Đánh giá trực quan phụ tải thiết bị và con người</div>
                                <div> - Dễ dàng phân công nhiệm vụ đến đối tượng phụ trách phù hợp</div>
                                <div> - Theo dõi công việc thông qua xem xét nhiệm vụ đã phân công và chưa phân công</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab2-2.png'); ?>" alt="Phân công nhiệm vụ có cơ sở" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Báo cáo sản xuất dễ hơn chơi game</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Báo cáo công việc trực tiếp và dễ dàng</div>
                                <div> - Thay thế giấy mực, không sợ mất, không mất công thu thập</div>
                                <div> - Nắm bắt chính xác giờ công từng người theo từng đơn hàng trên từng thiết bị, dễ dàng đánh giá năng suất nhân viên</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab2-3.jpg'); ?>" alt="Báo cáo sản xuất" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dgw-content content-article">
            <div class="dgw-content-list">
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Tổng quan tình hình nhà máy</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Tự do thiết kế nội dung cần theo dõi</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab3-1.jpg'); ?>" alt="Tổng quan tình hình nhà máy 1" loading="lazy" decoding="async" /></div>
                        </div>
                        <div class="dgw-article-content">
                            <div>
                                <div> - Dễ dàng nhận biết tình hình vận hành tổng quan trong nhà máy</div>
                                <div> - Nhân viên cũng có thể theo dõi tiến độ sản xuất</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab3-2.jpg'); ?>" alt="Tổng quan tình hình nhà máy 2" loading="lazy" decoding="async" /></div>
                        </div>
                        <div class="dgw-article-content">
                            <div>
                                <div> - Phân tích và theo dõi nguyên nhân NG</div>
                                <div> - Họp đánh giá với phương hướng rõ ràng</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab3-3.jpg'); ?>" alt="Phân tích và theo dõi nguyên nhân NG" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="dgw-content content-cases">
            <div class="dgw-content-list">
                <div class="dgw-content-item">
                    <h3 class="dgw-link">Điện thoại và Email là trợ lý đắc lực</h3>
                    <div class="dgw-article">
                        <div class="dgw-article-content">
                            <div>
                                <div> - Thông báo qua app AIoT Cloud</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab4-1.jpg'); ?>" alt="Thông báo qua app AIoT Cloud" loading="lazy" decoding="async" /></div>
                        </div>
                        <div class="dgw-article-content">
                            <div>
                                <div> - Thông báo trên hệ điều hành thiết bị di động</div>
                            </div>
                            <div><img src="<?php echo esc_url($diviImgDir . 'Tab4-2.png'); ?>" alt="Thông báo trên hệ điều hành thiết bị di động" loading="lazy" decoding="async" /></div>
                        </div>
                    </div>
                    <div class="dgw-article-content">
                        <div>
                            <div> - Thông báo qua email</div>
                        </div>
                        <div>
                            <img src="<?php echo esc_url($diviImgDir . 'Tab4-3.png'); ?>" alt="Thông báo qua email" loading="lazy" decoding="async" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
get_template_part('templates/footer');
get_footer();
