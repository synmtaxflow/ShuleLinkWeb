<?php

namespace App\Services;

class SmsService
{
    private $username = 'emcatechn';
    private $password = 'Emca@#12';
    private $senderID = 'ShuleLink';
    private $baseUrl = 'https://messaging-service.co.tz/link/sms/v1/text/single';

    /**
     * Send SMS to a phone number
     *
     * @param string $phoneNumber Phone number (e.g., 255614863345)
     * @param string $message Message to send
     * @return array Response with success status and message
     */
    public function sendSms($phoneNumber, $message)
    {
        try {
            // Remove any spaces or special characters from phone number
            $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
            
            // Ensure phone number starts with 255 (Tanzania country code)
            if (!str_starts_with($phoneNumber, '255')) {
                $phoneNumber = '255' . ltrim($phoneNumber, '0');
            }

            $text = urlencode($message);
            $url = $this->baseUrl . '?username=' . urlencode($this->username) . 
                   '&password=' . urlencode($this->password) . 
                   '&from=' . urlencode($this->senderID) . 
                   '&to=' . $phoneNumber . 
                   '&text=' . $text;

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));

            $response = curl_exec($curl);
            $error = curl_error($curl);
            $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            if ($error) {
                return [
                    'success' => false,
                    'message' => 'SMS sending failed: ' . $error
                ];
            }

            $responseData = json_decode($response, true);
            $isSuccess = ($httpCode == 200);
            $errorMessage = 'SMS sent successfully';

            if ($responseData && isset($responseData['messages'][0]['status'])) {
                $status = $responseData['messages'][0]['status'];
                // groupName can be PENDING, ACCEPTED, REJECTED, etc.
                if (isset($status['groupName']) && $status['groupName'] === 'REJECTED') {
                    $isSuccess = false;
                    $errorMessage = $status['description'] ?? 'Message was rejected by the gateway';
                }
            } elseif ($httpCode !== 200) {
                $isSuccess = false;
                $errorMessage = "Gateway returned HTTP code {$httpCode}";
            }

            return [
                'success' => $isSuccess,
                'message' => $errorMessage,
                'response' => $response,
                'http_code' => $httpCode
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'SMS sending exception: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Send welcome SMS to parent with credentials
     *
     * @param string $phoneNumber Parent's phone number
     * @param string $schoolName School name
     * @param string $username Username (phone number)
     * @param string $password Password (last name)
     * @return array
     */
    public function sendParentCredentials($phoneNumber, $schoolName, $username, $password)
    {
        $message = "{$schoolName}. Usajili umekamilika. Username: {$username}. Password: {$password}. Asante";
        
        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Send student credentials SMS to parent
     *
     * @param string $phoneNumber Parent's phone number
     * @param string $schoolName School name
     * @param string $studentName Student's full name
     * @param string $username Username (admission number)
     * @param string $password Password (last name)
     * @return array
     */
    public function sendStudentCredentials($phoneNumber, $schoolName, $studentName, $username, $password)
    {
        $message = "{$schoolName}. Mwanafunzi {$studentName} amesajiliwa kikamilifu. Username: {$username}. Password: {$password}. Asante";
        
        return $this->sendSms($phoneNumber, $message);
    }

    /**
     * Get SMS account balance
     *
     * @return array Response with balance information
     */
    public function getBalance()
    {
        try {
            // Try different possible balance endpoints
            $balanceUrls = [
                'https://messaging-service.co.tz/link/sms/v1/balance',
                'https://messaging-service.co.tz/api/balance',
                'https://messaging-service.co.tz/link/sms/v1/account/balance'
            ];

            foreach ($balanceUrls as $balanceUrl) {
                $url = $balanceUrl . '?username=' . urlencode($this->username) . 
                       '&password=' . urlencode($this->password);

                $curl = curl_init();

                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'GET',
                ));

                $response = curl_exec($curl);
                $error = curl_error($curl);
                $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                curl_close($curl);

                if (!$error && $httpCode == 200) {
                    // Try to parse response
                    $data = json_decode($response, true);
                    
                    // If response is JSON
                    if (json_last_error() === JSON_ERROR_NONE) {
                        return [
                            'success' => true,
                            'balance' => $data['balance'] ?? $data['credits'] ?? $data['amount'] ?? 0,
                            'currency' => $data['currency'] ?? 'TZS',
                            'response' => $data
                        ];
                    }
                    
                    // If response is plain text/number
                    $balance = trim($response);
                    if (is_numeric($balance)) {
                        return [
                            'success' => true,
                            'balance' => (float)$balance,
                            'currency' => 'TZS',
                            'response' => $response
                        ];
                    }
                }
            }

            // If all endpoints failed, return error
            return [
                'success' => false,
                'message' => 'Unable to fetch balance. Please check API endpoint.',
                'balance' => 0
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error fetching balance: ' . $e->getMessage(),
                'balance' => 0
            ];
        }
    }
}


