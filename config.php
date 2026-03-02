<?php
// --- DATABASE CONNECTION SETTINGS ---
// TODO: Replace the placeholder values below with your specific Azure configurations before running.
$serverName = "tcp:YOUR_DB_SERVER_NAME.database.windows.net,1433";
$database = "YOUR_DATABASE_NAME";                                                                                                                                                                                                                                             
$uid = "YOUR_DATABASE_USERNAME";                                                                                                                                                                                                                                                                                                                                                                                                                

// Ask IMDS for the Managed Identity Token
// Note: This URL uses a standard Azure internal IP for IMDS and is safe to leave public.
$imdsUrl = "http://169.254.169.254/metadata/identity/oauth2/token?api-version=2018-02-01&resource=https%3A%2F%2Fvault.azure.net";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $imdsUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array("Metadata: true"));
$tokenResponse = curl_exec($ch);
curl_close($ch);

$tokenData = json_decode($tokenResponse, true);
if (!isset($tokenData['access_token'])) {
    die("Security Error: Failed to acquire Managed Identity token. Ensure System-Assigned Identity is ON.");
}
$accessToken = $tokenData['access_token'];

// 2. Fetch the password from Key Vault
$keyVaultName = "YOUR_KEY_VAULT_NAME";
$secretName = "YOUR_SECRET_NAME";
$kvUrl = "https://{$keyVaultName}.vault.azure.net/secrets/{$secretName}?api-version=7.4";

$chKv = curl_init();
curl_setopt($chKv, CURLOPT_URL, $kvUrl);
curl_setopt($chKv, CURLOPT_RETURNTRANSFER, true);
curl_setopt($chKv, CURLOPT_HTTPHEADER, array("Authorization: Bearer " . $accessToken));
$secretResponse = curl_exec($chKv);
curl_close($chKv);
$secretData = json_decode($secretResponse, true);
if (!isset($secretData['value'])) {
     die("Security Error: Failed to retrieve secret from Key Vault. Check IAM Access Policies.");
}

// 3. Set the global variable for index.php to use
$pwd = $secretData['value'];
?>
