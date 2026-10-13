<?php
// 'momo' = dùng cổng MoMo sandbox (môi trường thử nghiệm) ; 'fake' = dùng cổng giả lập cũ (fake_gateway.php)
const PAYMENT_MODE = 'momo';

function momo_config(): array {
    return [
        'endpoint'    => 'https://test-payment.momo.vn/v2/gateway/api/create',
        // Bộ khóa test do giảng viên cung cấp (môi trường sandbox, không phải khóa thật).
        'partnerCode' => 'MOMO4MUD20240115_TEST',
        'accessKey'   => 'Ekj9og2VnRfOuIys',
        'secretKey'   => 'PseUbm2s8QVJEbexsh8H3Jz2qa9tDqoa',
        /* Bộ khóa test dự phòng (cũng có trong file mẫu của thầy), dùng khi bộ trên bị lỗi:
           partnerCode MOMOBKUN20180529 | accessKey klm05TvNBzhg7h7j | secretKey at67qH6mk8w5Y1nAyMoYKMWACiEi2bsa */
        // Máy XAMPP hay thiếu chứng chỉ SSL nên tạm tắt kiểm tra cho sandbox. Khi chạy thật PHẢI đặt true.
        'verify_ssl'  => false,
    ];
}
