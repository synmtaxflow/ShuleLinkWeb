# ZKTeco Device Setup Guide - Common Settings

## Error 10054: "Connection was forcibly closed by remote host"

Hii ina-maana device ime-close connection. Mara nyingi ni kwa sababu:
1. Comm Key si sahihi
2. Device protocol settings hazifanani
3. Device ina-require authentication before connection

---

## Common Device Settings (ZKTeco Standard)

### 1. Network Settings (System → Communication → Network)

**Device Settings:**
- **IP Address:** `192.168.100.108` (au IP yako ya device)
- **Subnet Mask:** `255.255.255.0`
- **Gateway:** `192.168.100.1` (au gateway ya network yako)
- **Port:** `4370` (default ZKTeco port)
- **DHCP:** `OFF` (use static IP - recommended)

**PC Settings (Windows):**
1. Open **Network Settings**
2. Right-click network adapter → **Properties**
3. Select **Internet Protocol Version 4 (TCP/IPv4)** → **Properties**
4. Set:
   - **IP Address:** `192.168.100.105` (au IP yako ya server)
   - **Subnet Mask:** `255.255.255.0`
   - **Default Gateway:** `192.168.100.1` (same as device)
   - **DNS:** `8.8.8.8` (Google DNS) au use gateway

**Verify:**
```bash
# Ping device from PC
ping 192.168.100.108

# Should return: Reply from 192.168.100.108
```

---

### 2. Comm Key Settings (System → Communication → Comm Key)

**Device Settings:**
- **Comm Key:** `0` (most common - no password)
- **OR** Set specific Comm Key (e.g., `12345`)

**Important:**
- Comm Key lazima i-match kwenye device na system
- Baadhi ya devices zina-default Comm Key ya `0`
- Baadhi za devices zina-require Comm Key ya `12345` au `8888`

**Check Device Comm Key:**
1. On device: **Menu → System → Communication → Comm Key**
2. Note the Comm Key value
3. Update system `.env` file:
   ```env
   ZKTECO_PASSWORD=0  # au value yako ya device
   ```

---

### 3. Protocol Settings (System → Communication → Protocol)

**Device Settings:**
- **Protocol:** `TCP/IP` (not UDP)
- **Connection Mode:** `Server` (device acts as server)
- **Port:** `4370`

**Verify:**
- Device ina-listen kwenye port 4370
- PC ina-connect TO device (not vice versa)

---

### 4. Device Time Settings

**Device Settings:**
- **System → Time → Date/Time**
- Set correct date and time
- **Time Zone:** Set correctly

**Why Important:**
- Baadhi ya devices zina-close connection ikiwa time si sahihi

---

### 5. Device Enable/Disable Settings

**Device Settings:**
- **System → System → Device Enable**
- Ensure device is **ENABLED** (not disabled)

**Check:**
- Device screen ina-show normal display (not "Device Disabled")

---

### 6. Firewall Settings (Windows PC)

**Windows Firewall:**
1. Open **Windows Defender Firewall**
2. Click **Advanced Settings**
3. **Inbound Rules:**
   - Allow port `4370` (TCP)
   - Allow port `8000` (TCP) - for Push SDK
4. **Outbound Rules:**
   - Allow port `4370` (TCP)
   - Allow port `8000` (TCP)

**Or Disable Firewall Temporarily (for testing only):**
1. Open **Windows Defender Firewall**
2. Turn off firewall (temporarily)
3. Test connection
4. Re-enable firewall after testing

---

### 7. Antivirus Settings

**Temporary Disable:**
- Disable antivirus temporarily kwa testing
- Baadhi ya antivirus zina-block socket connections

---

## Step-by-Step Setup Checklist

### On Device:

- [ ] **Network Settings:**
  - [ ] IP: `192.168.100.108`
  - [ ] Subnet: `255.255.255.0`
  - [ ] Gateway: `192.168.100.1`
  - [ ] Port: `4370`
  - [ ] DHCP: OFF

- [ ] **Comm Key:**
  - [ ] Check Comm Key value
  - [ ] Note the value (usually `0`)

- [ ] **Protocol:**
  - [ ] TCP/IP enabled
  - [ ] Port: `4370`

- [ ] **Time Settings:**
  - [ ] Date/Time correct
  - [ ] Time zone correct

- [ ] **Device Status:**
  - [ ] Device is ENABLED
  - [ ] Not in sleep mode

### On PC:

- [ ] **Network Settings:**
  - [ ] IP: `192.168.100.105` (same subnet as device)
  - [ ] Subnet: `255.255.255.0`
  - [ ] Gateway: `192.168.100.1`

- [ ] **Firewall:**
  - [ ] Port 4370 allowed (or firewall disabled for testing)

- [ ] **System Settings:**
  - [ ] `.env` file has correct Comm Key:
    ```env
    ZKTECO_IP=192.168.100.108
    ZKTECO_PORT=4370
    ZKTECO_PASSWORD=0  # au Comm Key ya device
    ```

---

## Testing Connection

### Step 1: Test Network Connectivity

```bash
# From PC, ping device
ping 192.168.100.108

# Should return: Reply from 192.168.100.108
# If timeout, check network settings
```

### Step 2: Test Port Connectivity

```bash
# Test if port 4370 is open
telnet 192.168.100.108 4370

# Or use PowerShell:
Test-NetConnection -ComputerName 192.168.100.108 -Port 4370
```

### Step 3: Test from System

1. Go to: **Student Registration → Device Connection Test**
2. Enter:
   - Device IP: `192.168.100.108`
   - Port: `4370`
   - Comm Key: `0` (au value ya device)
3. Click **Test Connection**

---

## Common Comm Key Values

Try these common Comm Key values:

1. **`0`** - Most common (no password)
2. **`12345`** - Common default
3. **`8888`** - Common default
4. **`0000`** - Some devices
5. **Empty/Blank** - Some devices

**How to find Comm Key:**
- Check device menu: **System → Communication → Comm Key**
- Check device manual/documentation
- Try common values above

---

## Troubleshooting Error 10054

### Solution 1: Check Comm Key

1. On device: **Menu → System → Communication → Comm Key**
2. Note exact value (including leading zeros)
3. Update `.env`:
   ```env
   ZKTECO_PASSWORD=0  # au exact value
   ```
4. Clear cache:
   ```bash
   php artisan config:clear
   ```
5. Test again

### Solution 2: Reset Device Network

1. On device: **Menu → System → Communication → Network**
2. Reset to factory defaults
3. Re-configure:
   - IP: `192.168.100.108`
   - Port: `4370`
   - Comm Key: `0`
4. Save and restart device

### Solution 3: Check Device Firmware

- Baadhi ya firmware versions zina-bugs
- Update device firmware ikiwa inawezekana
- Check device model compatibility

### Solution 4: Try Different Connection Method

1. Use ZKTeco official software kwa testing
2. Ikiwa official software ina-connect, tatizo ni kwenye code
3. Ikiwa official software pia ina-fail, tatizo ni kwenye device settings

---

## Device Model Specific Settings

### ZKTeco UF200-S / UF300
- Default Comm Key: `0`
- Port: `4370`
- Protocol: TCP/IP

### ZKTeco K Series
- Default Comm Key: `0` or `12345`
- Port: `4370`
- Protocol: TCP/IP

### ZKTeco F Series
- Default Comm Key: `0`
- Port: `4370`
- Protocol: TCP/IP

---

## Quick Fix Checklist

If connection still fails after above steps:

1. [ ] Device na PC ziko kwenye same network (same subnet)
2. [ ] Device IP na PC IP ziko kwenye same range (192.168.100.x)
3. [ ] Comm Key matches exactly (check for spaces, case sensitivity)
4. [ ] Port 4370 is open (test with telnet)
5. [ ] Firewall is disabled or port is allowed
6. [ ] Device is powered on and not in sleep mode
7. [ ] Device time is correct
8. [ ] Device is enabled (not disabled)
9. [ ] Try restarting device
10. [ ] Try restarting PC

---

## Contact Support

Ikiwa bado ina-fail:
1. Check device logs (if available)
2. Try with ZKTeco official software
3. Check device firmware version
4. Contact device manufacturer support

---

**Last Updated:** December 2024

