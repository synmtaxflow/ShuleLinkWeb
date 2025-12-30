<?php
/**
 * ZKLib - ZKTeco Device Communication Library
 * Based on popular ZKLib implementation for ZKTeco devices
 */

namespace App\Libraries;

use Illuminate\Support\Facades\Log;

class ZKLib
{
    private $ip;
    private $port;
    private $commKey;
    private $socket;
    private $sessionId = 0;
    private $replyId = 0;
    private $connected = false;

    // Protocol Constants
    const CMD_CONNECT = 0x1000;
    const CMD_EXIT = 0x0006;
    const CMD_ACK = 0x8000;
    const CMD_ACK_OK = 0x8001;
    const CMD_ACK_ERROR = 0x8002;
    const CMD_ACK_UNAUTH = 0x8005;
    const CMD_ACK_DATA = 0x8002;
    const CMD_ACK_RETRY = 0x8003;
    const CMD_ACK_REPEAT = 0x8004;
    const CMD_ACK_UNKNOWN = 0x8005;
    const CMD_ACK_ERROR_CMD = 0x8006;
    const CMD_ACK_ERROR_INIT = 0x8007;
    const CMD_ACK_ERROR_DATA = 0x8008;
    const CMD_USER_WRQ = 0x005A;
    const CMD_USERTEMP_RRQ = 0x005B;
    const CMD_USERTEMP_WRQ = 0x005C;
    const CMD_ATTLOG_RRQ = 0x0080;
    const CMD_CLEAR_DATA = 0x0081;
    const CMD_CLEAR_ATTLOG = 0x0082;
    const CMD_GET_TIME = 0x00A0;
    const CMD_SET_TIME = 0x00A1;
    const CMD_VERSION = 0x00A2;
    const CMD_DEVICE = 0x00A3;
    const USHRT_MAX = 65535;
    const CMD_AUTH = 0x0005;

    public function __construct($ip, $port = 4370, $commKey = 0)
    {
        $this->ip = $ip;
        $this->port = $port;
        $this->commKey = $commKey;
    }

    /**
     * Connect to device
     */
    public function connect()
    {
        if (!extension_loaded('sockets')) {
            throw new \Exception('PHP Sockets extension is not enabled');
        }

        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($this->socket === false) {
            Log::error('ZKLib: Failed to create socket');
            return false;
        }

        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 5, 'usec' => 0]);
        socket_set_option($this->socket, SOL_SOCKET, SO_SNDTIMEO, ['sec' => 5, 'usec' => 0]);

        $connected = @socket_connect($this->socket, $this->ip, $this->port);
        if (!$connected) {
            Log::error('ZKLib: Failed to connect to device');
            @socket_close($this->socket);
            $this->socket = null;
            return false;
        }

        Log::info('ZKLib: TCP connection established');

        // Send CONNECT command
        $command = $this->createCommand(self::CMD_CONNECT, '');
        $sent = @socket_write($this->socket, $command, strlen($command));
        
        if ($sent === false) {
            Log::error('ZKLib: Failed to send CONNECT command');
            @socket_close($this->socket);
            $this->socket = null;
            return false;
        }

        // Receive response
        $response = $this->receive();
        if ($response === false) {
            Log::error('ZKLib: No response to CONNECT command');
            @socket_close($this->socket);
            $this->socket = null;
            return false;
        }

        // Parse response
        $header = substr($response, 0, 8);
        $unpacked = unpack('vcmd/vchksum/vid/vid2', $header);
        
        $this->sessionId = $unpacked['id'];
        $this->replyId = $unpacked['id2'];

        Log::info('ZKLib: CONNECT successful', [
            'session_id' => $this->sessionId,
            'reply_id' => $this->replyId
        ]);

        // Authenticate
        if (!$this->authenticate()) {
            Log::error('ZKLib: Authentication failed');
            @socket_close($this->socket);
            $this->socket = null;
            return false;
        }

        $this->connected = true;
        Log::info('ZKLib: Successfully connected and authenticated');
        return true;
    }

    /**
     * Authenticate with device
     */
    private function authenticate()
    {
        $password = str_pad((string)$this->commKey, 8, "\0", STR_PAD_RIGHT);
        $command = $this->createCommand(self::CMD_AUTH, $password);
        
        $sent = @socket_write($this->socket, $command, strlen($command));
        if ($sent === false) {
            Log::error('ZKLib: Failed to send AUTH command');
            return false;
        }

        $response = $this->receive();
        if ($response === false) {
            Log::error('ZKLib: No response to AUTH command');
            return false;
        }

        $header = substr($response, 0, 8);
        $unpacked = unpack('vcmd/vchksum/vid/vid2', $header);
        
        if ($unpacked['cmd'] == self::CMD_ACK_OK) {
            $this->sessionId = $unpacked['id'];
            $this->replyId = $unpacked['id2'];
            Log::info('ZKLib: Authentication successful');
            return true;
        }

        Log::error('ZKLib: Authentication failed', ['response_cmd' => '0x' . dechex($unpacked['cmd'])]);
        return false;
    }

    /**
     * Create command packet
     */
    private function createCommand($command, $data = '')
    {
        $this->replyId++;
        if ($this->replyId > self::USHRT_MAX) {
            $this->replyId = 1;
        }

        $dataLength = strlen($data);
        $header = pack('v', $command);
        $header .= pack('v', 0); // checksum placeholder
        $header .= pack('v', $this->sessionId);
        $header .= pack('v', $this->replyId);
        $header .= pack('V', $dataLength);

        $checksum = $this->calculateChecksum($header);
        
        $packet = pack('v', $command);
        $packet .= pack('v', $checksum);
        $packet .= pack('v', $this->sessionId);
        $packet .= pack('v', $this->replyId);
        $packet .= pack('V', $dataLength);
        $packet .= $data;

        return $packet;
    }

    /**
     * Calculate checksum
     */
    private function calculateChecksum($data)
    {
        $checksum = 0;
        for ($i = 0; $i < strlen($data); $i++) {
            $checksum += ord($data[$i]);
        }
        return $checksum & 0xFFFF;
    }

    /**
     * Receive data from socket
     */
    private function receive($timeout = 5)
    {
        socket_set_option($this->socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => $timeout, 'usec' => 0]);
        
        $header = @socket_read($this->socket, 8, PHP_BINARY_READ);
        if ($header === false || strlen($header) != 8) {
            return false;
        }

        $unpacked = unpack('vcmd/vchksum/vid/vid2/Vsize', $header);
        $size = $unpacked['size'];

        $data = '';
        if ($size > 0) {
            $data = @socket_read($this->socket, $size, PHP_BINARY_READ);
            if ($data === false) {
                return false;
            }
        }

        return $header . $data;
    }

    /**
     * Get device info
     */
    public function getDeviceInfo()
    {
        if (!$this->connected) {
            if (!$this->connect()) {
                return false;
            }
        }

        try {
            $info = [
                'device_name' => null,
                'device_id' => null,
                'serial_number' => null,
                'version' => null,
                'firmware_version' => null,
                'platform' => null,
                'ip' => $this->ip,
                'port' => $this->port,
                'comm_key' => $this->commKey
            ];
            
            // Try CMD_VERSION (0x00A2) first
            $command = $this->createCommand(self::CMD_VERSION, '');
            $sent = @socket_write($this->socket, $command, strlen($command));
            
            if ($sent !== false) {
                $response = $this->receive(5);
                
                if ($response && strlen($response) > 8) {
                    $data = substr($response, 8);
                    $info = $this->parseDeviceInfo($data);
                    $info['ip'] = $this->ip;
                    $info['port'] = $this->port;
                    $info['comm_key'] = $this->commKey;
                    
                    if ($info['device_name'] || $info['serial_number'] || $info['version']) {
                        Log::info('ZKLib: Successfully retrieved device info using CMD_VERSION');
                        return $info;
                    }
                }
            }
            
            // Try CMD_DEVICE (0x00A3) as fallback
            $command = $this->createCommand(self::CMD_DEVICE, '');
            $sent = @socket_write($this->socket, $command, strlen($command));
            
            if ($sent !== false) {
                $response = $this->receive(5);
                
                if ($response && strlen($response) > 8) {
                    $data = substr($response, 8);
                    $info = $this->parseDeviceInfo($data);
                    $info['ip'] = $this->ip;
                    $info['port'] = $this->port;
                    $info['comm_key'] = $this->commKey;
                    
                    if ($info['device_name'] || $info['serial_number'] || $info['version']) {
                        Log::info('ZKLib: Successfully retrieved device info using CMD_DEVICE');
                        return $info;
                    }
                }
            }
            
            Log::warning('ZKLib: Could not retrieve device info');
            return $info;
            
        } catch (\Exception $e) {
            Log::error('ZKLib Get Device Info Error: ' . $e->getMessage());
            return [
                'ip' => $this->ip,
                'port' => $this->port,
                'comm_key' => $this->commKey,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Parse device info from response data
     */
    private function parseDeviceInfo($data)
    {
        $info = [
            'device_name' => null,
            'device_id' => null,
            'serial_number' => null,
            'version' => null,
            'firmware_version' => null,
            'platform' => null
        ];
        
        try {
            $dataLength = strlen($data);
            Log::debug("ZKLib: Parsing device info", [
                'data_length' => $dataLength,
                'data_hex' => bin2hex(substr($data, 0, 100))
            ]);
            
            // Extract strings from data
            $strings = [];
            $currentString = '';
            
            for ($i = 0; $i < $dataLength; $i++) {
                $char = $data[$i];
                $ascii = ord($char);
                
                if (($ascii >= 32 && $ascii <= 126) || $ascii == 0) {
                    if ($ascii == 0) {
                        if (strlen($currentString) >= 3) {
                            $strings[] = trim($currentString);
                        }
                        $currentString = '';
                    } else {
                        $currentString .= $char;
                    }
                } else {
                    if (strlen($currentString) >= 3) {
                        $strings[] = trim($currentString);
                    }
                    $currentString = '';
                }
            }
            
            if (strlen($currentString) >= 3) {
                $strings[] = trim($currentString);
            }
            
            Log::debug("ZKLib: Extracted strings", ['strings' => $strings, 'count' => count($strings)]);
            
            // Identify device info from strings
            foreach ($strings as $str) {
                if (empty($str)) continue;
                
                // Device name
                if (preg_match('/^(UF|ZK|iFace|K|F|V|X|C|T|P)[\d\-]+/i', $str) || 
                    preg_match('/\b(Device|Terminal|Machine)\b/i', $str)) {
                    if (!$info['device_name']) {
                        $info['device_name'] = $str;
                    }
                }
                // Serial number
                elseif (preg_match('/^[A-Z]{2,4}\d{8,12}$/i', $str) || 
                        (strlen($str) >= 10 && strlen($str) <= 20 && preg_match('/^[A-Z0-9]+$/i', $str))) {
                    if (!$info['serial_number']) {
                        $info['serial_number'] = $str;
                    }
                }
                // Version/Firmware
                elseif (preg_match('/^Ver\s+/i', $str) || preg_match('/\d+\.\d+/', $str)) {
                    if (!$info['version'] && !$info['firmware_version']) {
                        $info['version'] = $str;
                        $info['firmware_version'] = $str;
                    }
                }
                // Platform
                elseif (preg_match('/\b(ZLM|ARM|X86|MIPS|PLATFORM)\b/i', $str) || 
                        preg_match('/_[A-Z0-9]+$/i', $str)) {
                    if (!$info['platform']) {
                        $info['platform'] = $str;
                    }
                }
            }
            
            // Try fixed-position parsing if strings method didn't work
            if (!$info['device_name'] && !$info['serial_number'] && !$info['version']) {
                if ($dataLength >= 72) {
                    $info['device_name'] = trim(substr($data, 0, 24), "\0") ?: null;
                    $info['serial_number'] = trim(substr($data, 24, 16), "\0") ?: null;
                    $info['version'] = trim(substr($data, 40, 16), "\0") ?: null;
                    $info['firmware_version'] = $info['version'];
                    $info['platform'] = trim(substr($data, 56, 16), "\0") ?: null;
                } elseif ($dataLength >= 48) {
                    $info['device_name'] = trim(substr($data, 0, 16), "\0") ?: null;
                    $info['serial_number'] = trim(substr($data, 16, 16), "\0") ?: null;
                    $info['version'] = trim(substr($data, 32, 16), "\0") ?: null;
                    $info['firmware_version'] = $info['version'];
                }
            }
            
            // Clean up null values
            foreach ($info as $key => $value) {
                if ($value === '' || $value === "\0" || $value === false || (is_string($value) && trim($value) === '')) {
                    $info[$key] = null;
                }
            }
            
        } catch (\Exception $e) {
            Log::error('ZKLib: Error parsing device info: ' . $e->getMessage());
        }
        
        return $info;
    }

    /**
     * Disconnect from device
     */
    public function disconnect()
    {
        if ($this->socket && $this->connected) {
            try {
                $command = $this->createCommand(self::CMD_EXIT, '');
                @socket_write($this->socket, $command, strlen($command));
            } catch (\Exception $e) {
                Log::warning('ZKLib: Error during disconnect - ' . $e->getMessage());
            }
            @socket_close($this->socket);
            $this->socket = null;
            $this->connected = false;
        }
    }

    /**
     * Check if connected
     */
    public function isConnected()
    {
        return $this->connected && $this->socket !== null;
    }

    /**
     * Get users from device
     * Uses CMD_USERTEMP_RRQ (0x005B) to retrieve all users
     */
    public function getUsers()
    {
        if (!$this->connected) {
            Log::error('ZKLib: Cannot get users - not connected');
            return false;
        }

        try {
            Log::info('ZKLib: Requesting users from device');
            
            // Send GET_USER command (CMD_USERTEMP_RRQ = 0x005B)
            $command = $this->createCommand(self::CMD_USERTEMP_RRQ, '');
            $sent = @socket_write($this->socket, $command, strlen($command));
            
            if ($sent === false) {
                Log::error('ZKLib: Failed to send GET_USER command');
                return false;
            }

            // Receive response
            $response = $this->receive(10); // Longer timeout for user data
            
            if ($response === false) {
                Log::error('ZKLib: No response to GET_USER command');
                return false;
            }

            if (strlen($response) < 8) {
                Log::error('ZKLib: Invalid response length for GET_USER');
                return false;
            }

            // Parse response header
            $header = substr($response, 0, 8);
            $unpacked = unpack('vcmd/vchksum/vid/vid2/Vsize', $header);
            $response_cmd = $unpacked['cmd'];
            $size = $unpacked['size'];

            Log::debug('ZKLib: GET_USER response', [
                'response_cmd' => '0x' . dechex($response_cmd),
                'data_size' => $size
            ]);

            // Check if response is OK and has data
            if (($response_cmd == self::CMD_ACK_DATA || $response_cmd == self::CMD_ACK_OK) && $size > 0) {
                $data = substr($response, 8);
                $users = $this->parseUsersFromData($data);
                
                Log::info('ZKLib: Retrieved ' . count($users) . ' users from device');
                return $users;
            }

            Log::warning('ZKLib: GET_USER command failed or no data', [
                'response_cmd' => '0x' . dechex($response_cmd),
                'size' => $size
            ]);
            
            return [];
            
        } catch (\Exception $e) {
            Log::error('ZKLib: Error getting users - ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parse users from binary data
     * User structure: uid (2 bytes), name (24 bytes), privilege (1 byte), password (8 bytes), group (8 bytes), user_id (8 bytes)
     * Total: 51 bytes per user
     */
    private function parseUsersFromData($data)
    {
        $users = [];
        $offset = 0;
        $userSize = 51; // 2 + 24 + 1 + 8 + 8 + 8
        
        while ($offset + $userSize <= strlen($data)) {
            $userData = substr($data, $offset, $userSize);
            
            try {
                $user = unpack('vuid/a24name/Cprivilege/a8password/a8group/a8user_id', $userData);
                
                // Only add valid users (uid > 0)
                if ($user['uid'] > 0) {
                    $users[] = [
                        'uid' => $user['uid'],
                        'name' => trim($user['name']),
                        'privilege' => $user['privilege'],
                        'password' => trim($user['password']),
                        'group' => trim($user['group']),
                        'user_id' => trim($user['user_id'])
                    ];
                }
            } catch (\Exception $e) {
                Log::warning('ZKLib: Error parsing user at offset ' . $offset . ': ' . $e->getMessage());
            }
            
            $offset += $userSize;
        }
        
        return $users;
    }
}
