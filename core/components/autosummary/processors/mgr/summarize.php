<?php
/**
 * Processor: Summarize Resource Content using ChatGPT.
 * Expects the resource ID via $_REQUEST['id'].
 */

// Check permissions.
if (!$modx->hasPermission('edit_document')) {
    return $modx->error->failure('Permission denied.');
}

$id = $modx->getOption('id', $_REQUEST, 0);
if (empty($id)) {
    return $modx->error->failure('No resource specified.');
}

// Fetch the resource.
$resource = $modx->getObject('modResource', $id);
if (!$resource) {
    return $modx->error->failure('Resource not found.');
}

// Retrieve the resource content.
$content = $resource->getContent();
if (empty($content)) {
    $modx->log(modX::LOG_LEVEL_WARN, 'AutoSummaryProcessor: Resource content is empty for resource ID ' . $id);
}

// Get the ChatGPT API key from system settings (set this key in MODX System Settings with key 'chatgpt_api_key').
$chatgptApiKey = $modx->getOption('chatgpt_api_key');
if (empty($chatgptApiKey)) {
    return $modx->error->failure('ChatGPT API key is not configured.');
}

// Prepare the prompt.
$prompt = "Summarize the following content into a concise, SEO optimized meta description:\n\n" . $content;

// Log the prompt for debugging.
$modx->log(modX::LOG_LEVEL_INFO, 'AutoSummaryProcessor: Prompt for ChatGPT: ' . $prompt);

// Set up the payload for the ChatGPT API.
$data = [
    'model' => 'gpt-3.5-turbo',
    'messages' => [
        [
            'role' => 'system',
            'content' => 'You are an assistant that creates SEO optimized meta descriptions.'
        ],
        [
            'role' => 'user',
            'content' => $prompt
        ]
    ],
    'max_tokens' => 60,
    'temperature' => 0.7
];

// Execute the API call using cURL.
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $chatgptApiKey
]);

$response = curl_exec($ch);
if (curl_errno($ch)) {
    $error_msg = curl_error($ch);
    curl_close($ch);
    $modx->log(modX::LOG_LEVEL_ERROR, 'AutoSummaryProcessor: cURL error: ' . $error_msg);
    return $modx->error->failure('cURL error: ' . $error_msg);
}
curl_close($ch);

// Log the raw response for debugging.
$modx->log(modX::LOG_LEVEL_INFO, 'AutoSummaryProcessor: Raw response: ' . $response);

$result = json_decode($response, true);
if (!isset($result['choices'][0]['message']['content'])) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'AutoSummaryProcessor: ChatGPT API error, result: ' . print_r($result, true));
    return $modx->error->failure('Error from ChatGPT API: ' . print_r($result, true));
}

$summary = trim($result['choices'][0]['message']['content']);
$modx->log(modX::LOG_LEVEL_INFO, 'AutoSummaryProcessor: Summary generated: ' . $summary);

// Return the summary to the AJAX caller.
return $modx->error->success('', ['summary' => $summary]);