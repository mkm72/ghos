<?php
function sendEmail($toEmail, $toName, $subject, $body) {
    $apiKey = 'your_resend_api_key';
    
    $data = [
        'from'    => 'noreply@ghos.shop',
        'to'      => [$toEmail],
        'subject' => $subject,
        'html'    => $body
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
