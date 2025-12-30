# Parent Fees and Payments API Documentation

## Overview
This API allows parents to retrieve payment records and fees summary for their children through a Flutter mobile application.

**Base URL:** `http://your-server-ip/api`

## Authentication
This API requires authentication. You need to login first using the `/api/login` endpoint to get parent credentials (parentID and schoolID).

---

## Endpoints

### 1. Get Parent Payments
Retrieve payment records for a parent's children.

**Endpoint:** `GET /api/parent/payments` or `POST /api/parent/payments`

### 2. Get Parent Fees Summary
Retrieve fees summary including required fees, payments, and installments.

**Endpoint:** `GET /api/parent/fees-summary` or `POST /api/parent/fees-summary`

---

## 1. Get Parent Payments API

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `parentID` | integer | Yes | Parent ID obtained from login |
| `schoolID` | integer | Yes | School ID obtained from login |
| `student` | integer | No | Filter by student ID |
| `year` | string | No | Filter by year (default: current year, e.g., "2024") |
| `fee_type` | string | No | Filter by fee type: `Tuition Fees` or `Other Fees` |
| `search` | string | No | Search by student name or admission number |

### Example Request (GET)
```
GET /api/parent/payments?parentID=4&schoolID=3&year=2024&fee_type=Tuition Fees
```

### Example Request (POST)
```json
{
  "parentID": 4,
  "schoolID": 3,
  "student": 12,
  "year": "2024",
  "fee_type": "Tuition Fees",
  "search": "John"
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Payments retrieved successfully",
  "data": {
    "parent": {
      "parentID": 4,
      "first_name": "John",
      "middle_name": "Doe",
      "last_name": "Smith",
      "phone": "+255123456789",
      "email": "john@example.com"
    },
    "school": {
      "schoolID": 3,
      "school_name": "Example School"
    },
    "students": [
      {
        "studentID": 12,
        "first_name": "Jane",
        "middle_name": "",
        "last_name": "Smith",
        "admission_number": "ST001",
        "photo": "http://server-url/userImages/photo.jpg",
        "class": "Form One",
        "subclass": "A"
      }
    ],
    "years": ["2024", "2023", "2022"],
    "filters": {
      "student": "",
      "year": "2024",
      "fee_type": "Tuition Fees",
      "search": ""
    },
    "statistics": {
      "total_payments": 2,
      "pending_payments": 1,
      "incomplete_payments": 0,
      "paid_payments": 1,
      "total_required": 500000,
      "total_paid": 250000,
      "total_balance": 250000,
      "tuition_fees_total": 400000,
      "other_fees_total": 100000
    },
    "payments": [
      {
        "index": 1,
        "student": {
          "studentID": 12,
          "first_name": "Jane",
          "middle_name": "",
          "last_name": "Smith",
          "admission_number": "ST001",
          "photo": "http://server-url/userImages/photo.jpg",
          "class": "Form One",
          "subclass": "A"
        },
        "payment": {
          "paymentID": 45,
          "fee_type": "Tuition Fees",
          "control_number": "3120T123456",
          "amount_required": 400000,
          "amount_paid": 200000,
          "balance": 200000,
          "payment_status": "Incomplete Payment",
          "payment_date": "2024-03-15",
          "payment_reference": "REF123456",
          "notes": "Payment in installments",
          "created_at": "2024-01-15 10:30:00"
        }
      }
    ]
  }
}
```

### Payment Status Values
- `Pending` - No payment made yet
- `Incomplete Payment` - Partial payment made
- `Partial` - Partial payment (same as Incomplete Payment)
- `Paid` - Full payment completed

### Fee Type Values
- `Tuition Fees` - Tuition fees for the student
- `Other Fees` - Other miscellaneous fees (uniforms, books, etc.)

---

## 2. Get Parent Fees Summary API

### Request Parameters

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `parentID` | integer | Yes | Parent ID obtained from login |
| `schoolID` | integer | Yes | School ID obtained from login |
| `year` | string | No | Filter by year (default: current year, e.g., "2024") |

### Example Request (GET)
```
GET /api/parent/fees-summary?parentID=4&schoolID=3&year=2024
```

### Example Request (POST)
```json
{
  "parentID": 4,
  "schoolID": 3,
  "year": "2024"
}
```

### Success Response (200 OK)

```json
{
  "success": true,
  "message": "Fees summary retrieved successfully",
  "data": {
    "parent": {
      "parentID": 4,
      "first_name": "John",
      "middle_name": "Doe",
      "last_name": "Smith",
      "phone": "+255123456789",
      "email": "john@example.com"
    },
    "school": {
      "schoolID": 3,
      "school_name": "Example School"
    },
    "years": ["2024", "2023", "2022"],
    "filters": {
      "year": "2024"
    },
    "summary": [
      {
        "studentID": 12,
        "student_name": "Jane Smith",
        "admission_number": "ST001",
        "photo": "http://server-url/userImages/photo.jpg",
        "class": "Form One",
        "tuition_fees": {
          "required": 400000,
          "paid": 200000,
          "balance": 200000,
          "control_number": "3120T123456",
          "status": "Incomplete Payment",
          "installments": [
            {
              "installmentID": 1,
              "installment_name": "First Term",
              "installment_type": "Termly",
              "amount": 150000
            },
            {
              "installmentID": 2,
              "installment_name": "Second Term",
              "installment_type": "Termly",
              "amount": 150000
            },
            {
              "installmentID": 3,
              "installment_name": "Third Term",
              "installment_type": "Termly",
              "amount": 100000
            }
          ],
          "allow_partial_payment": true
        },
        "other_fees": {
          "required": 100000,
          "paid": 0,
          "balance": 100000,
          "control_number": null,
          "status": "No Payment",
          "installments": [],
          "allow_partial_payment": false,
          "other_fees_details": [
            {
              "detailID": 1,
              "fee_detail_name": "School Uniform",
              "amount": 50000,
              "description": "Full school uniform set"
            },
            {
              "detailID": 2,
              "fee_detail_name": "Books and Stationery",
              "amount": 30000,
              "description": "Required textbooks and stationery"
            },
            {
              "detailID": 3,
              "fee_detail_name": "Development Fee",
              "amount": 20000,
              "description": "School development contribution"
            }
          ]
        },
        "total": {
          "required": 500000,
          "paid": 200000,
          "balance": 300000
        }
      }
    ]
  }
}
```

### Installment Type Values
- `Termly` - Payment per term
- `Monthly` - Payment per month
- `Quarterly` - Payment per quarter
- `Semester` - Payment per semester
- `Custom` - Custom installment plan

---

## Error Responses

### 400 Bad Request
```json
{
  "success": false,
  "message": "parentID and schoolID are required"
}
```

### 404 Not Found
```json
{
  "success": false,
  "message": "Parent not found"
}
```

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Error retrieving payments: [error details]"
}
```

---

## Flutter Integration Examples

### 1. Get Parent Payments

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class ParentPaymentsAPI {
  final String baseUrl = 'http://your-server-ip/api';
  
  Future<Map<String, dynamic>> getParentPayments({
    required int parentID,
    required int schoolID,
    int? studentID,
    String? year,
    String? feeType,
    String? search,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/parent/payments').replace(
        queryParameters: {
          'parentID': parentID.toString(),
          'schoolID': schoolID.toString(),
          if (studentID != null) 'student': studentID.toString(),
          if (year != null) 'year': year,
          if (feeType != null) 'fee_type': feeType,
          if (search != null && search.isNotEmpty) 'search': search,
        },
      );
      
      final response = await http.get(uri);
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Failed to fetch payments');
        }
      } else {
        throw Exception('HTTP Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
```

### 2. Get Parent Fees Summary

```dart
class ParentFeesAPI {
  final String baseUrl = 'http://your-server-ip/api';
  
  Future<Map<String, dynamic>> getParentFeesSummary({
    required int parentID,
    required int schoolID,
    String? year,
  }) async {
    try {
      final uri = Uri.parse('$baseUrl/parent/fees-summary').replace(
        queryParameters: {
          'parentID': parentID.toString(),
          'schoolID': schoolID.toString(),
          if (year != null) 'year': year,
        },
      );
      
      final response = await http.get(uri);
      
      if (response.statusCode == 200) {
        final data = json.decode(response.body);
        if (data['success'] == true) {
          return data['data'];
        } else {
          throw Exception(data['message'] ?? 'Failed to fetch fees summary');
        }
      } else {
        throw Exception('HTTP Error: ${response.statusCode}');
      }
    } catch (e) {
      throw Exception('Error: $e');
    }
  }
}
```

### Using Dio Package

```dart
import 'package:dio/dio.dart';

class ParentPaymentsAPI {
  final Dio dio = Dio(BaseOptions(
    baseUrl: 'http://your-server-ip/api',
    connectTimeout: 5000,
    receiveTimeout: 3000,
  ));
  
  Future<Map<String, dynamic>> getParentPayments({
    required int parentID,
    required int schoolID,
    int? studentID,
    String? year,
    String? feeType,
    String? search,
  }) async {
    try {
      final response = await dio.get(
        '/parent/payments',
        queryParameters: {
          'parentID': parentID,
          'schoolID': schoolID,
          if (studentID != null) 'student': studentID,
          if (year != null) 'year': year,
          if (feeType != null) 'fee_type': feeType,
          if (search != null && search.isNotEmpty) 'search': search,
        },
      );
      
      if (response.data['success'] == true) {
        return response.data['data'];
      } else {
        throw Exception(response.data['message'] ?? 'Failed to fetch payments');
      }
    } on DioException catch (e) {
      throw Exception('Error: ${e.message}');
    }
  }
  
  Future<Map<String, dynamic>> getParentFeesSummary({
    required int parentID,
    required int schoolID,
    String? year,
  }) async {
    try {
      final response = await dio.get(
        '/parent/fees-summary',
        queryParameters: {
          'parentID': parentID,
          'schoolID': schoolID,
          if (year != null) 'year': year,
        },
      );
      
      if (response.data['success'] == true) {
        return response.data['data'];
      } else {
        throw Exception(response.data['message'] ?? 'Failed to fetch fees summary');
      }
    } on DioException catch (e) {
      throw Exception('Error: ${e.message}');
    }
  }
}
```

---

## Flutter Widget Examples

### Payments List Widget

```dart
class PaymentsListScreen extends StatefulWidget {
  final int parentID;
  final int schoolID;
  
  @override
  _PaymentsListScreenState createState() => _PaymentsListScreenState();
}

class _PaymentsListScreenState extends State<PaymentsListScreen> {
  final api = ParentPaymentsAPI();
  Map<String, dynamic>? paymentsData;
  bool isLoading = true;
  
  @override
  void initState() {
    super.initState();
    loadPayments();
  }
  
  Future<void> loadPayments() async {
    try {
      setState(() => isLoading = true);
      final data = await api.getParentPayments(
        parentID: widget.parentID,
        schoolID: widget.schoolID,
        year: '2024',
      );
      setState(() {
        paymentsData = data;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Center(child: CircularProgressIndicator());
    }
    
    if (paymentsData == null) {
      return Center(child: Text('No payments available'));
    }
    
    final payments = paymentsData!['payments'] as List;
    final stats = paymentsData!['statistics'];
    
    return Scaffold(
      appBar: AppBar(title: Text('Payments')),
      body: Column(
        children: [
          // Statistics Card
          Card(
            child: Padding(
              padding: EdgeInsets.all(16),
              child: Column(
                children: [
                  Text('Total Required: ${stats['total_required']}'),
                  Text('Total Paid: ${stats['total_paid']}'),
                  Text('Total Balance: ${stats['total_balance']}'),
                  Text('Pending: ${stats['pending_payments']}'),
                  Text('Paid: ${stats['paid_payments']}'),
                ],
              ),
            ),
          ),
          // Payments List
          Expanded(
            child: ListView.builder(
              itemCount: payments.length,
              itemBuilder: (context, index) {
                final item = payments[index];
                final student = item['student'];
                final payment = item['payment'];
                
                return Card(
                  child: ListTile(
                    leading: CircleAvatar(
                      backgroundImage: student['photo'] != null
                          ? NetworkImage(student['photo'])
                          : null,
                    ),
                    title: Text('${student['first_name']} ${student['last_name']}'),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('${student['admission_number']} - ${student['class']}'),
                        Text('Control: ${payment['control_number']}'),
                        Text('Type: ${payment['fee_type']}'),
                        Text('Status: ${payment['payment_status']}'),
                      ],
                    ),
                    trailing: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text('${payment['amount_paid']} / ${payment['amount_required']}'),
                        Text('Balance: ${payment['balance']}',
                          style: TextStyle(
                            color: payment['balance'] > 0 ? Colors.red : Colors.green,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}
```

### Fees Summary Widget

```dart
class FeesSummaryScreen extends StatefulWidget {
  final int parentID;
  final int schoolID;
  
  @override
  _FeesSummaryScreenState createState() => _FeesSummaryScreenState();
}

class _FeesSummaryScreenState extends State<FeesSummaryScreen> {
  final api = ParentFeesAPI();
  Map<String, dynamic>? feesData;
  bool isLoading = true;
  
  @override
  void initState() {
    super.initState();
    loadFeesSummary();
  }
  
  Future<void> loadFeesSummary() async {
    try {
      setState(() => isLoading = true);
      final data = await api.getParentFeesSummary(
        parentID: widget.parentID,
        schoolID: widget.schoolID,
        year: '2024',
      );
      setState(() {
        feesData = data;
        isLoading = false;
      });
    } catch (e) {
      setState(() => isLoading = false);
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error: $e')),
      );
    }
  }
  
  @override
  Widget build(BuildContext context) {
    if (isLoading) {
      return Center(child: CircularProgressIndicator());
    }
    
    if (feesData == null) {
      return Center(child: Text('No fees data available'));
    }
    
    final summary = feesData!['summary'] as List;
    
    return Scaffold(
      appBar: AppBar(title: Text('Fees Summary')),
      body: ListView.builder(
        itemCount: summary.length,
        itemBuilder: (context, index) {
          final studentSummary = summary[index];
          final tuition = studentSummary['tuition_fees'];
          final other = studentSummary['other_fees'];
          final total = studentSummary['total'];
          
          return Card(
            child: ExpansionTile(
              leading: CircleAvatar(
                backgroundImage: studentSummary['photo'] != null
                    ? NetworkImage(studentSummary['photo'])
                    : null,
              ),
              title: Text(studentSummary['student_name']),
              subtitle: Text('${studentSummary['admission_number']} - ${studentSummary['class']}'),
              children: [
                // Tuition Fees
                ListTile(
                  title: Text('Tuition Fees'),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Required: ${tuition['required']}'),
                      Text('Paid: ${tuition['paid']}'),
                      Text('Balance: ${tuition['balance']}'),
                      Text('Status: ${tuition['status']}'),
                      if (tuition['control_number'] != null)
                        Text('Control: ${tuition['control_number']}'),
                    ],
                  ),
                  trailing: _buildStatusChip(tuition['status']),
                ),
                // Installments
                if (tuition['installments'].length > 0)
                  ...tuition['installments'].map<Widget>((installment) => ListTile(
                    title: Text(installment['installment_name']),
                    subtitle: Text(installment['installment_type']),
                    trailing: Text('${installment['amount']}'),
                  )).toList(),
                
                // Other Fees
                ListTile(
                  title: Text('Other Fees'),
                  subtitle: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Required: ${other['required']}'),
                      Text('Paid: ${other['paid']}'),
                      Text('Balance: ${other['balance']}'),
                      Text('Status: ${other['status']}'),
                      if (other['control_number'] != null)
                        Text('Control: ${other['control_number']}'),
                    ],
                  ),
                  trailing: _buildStatusChip(other['status']),
                ),
                // Other Fees Details
                if (other['other_fees_details'].length > 0)
                  ...other['other_fees_details'].map<Widget>((detail) => ListTile(
                    title: Text(detail['fee_detail_name']),
                    subtitle: Text(detail['description'] ?? ''),
                    trailing: Text('${detail['amount']}'),
                  )).toList(),
                
                // Total
                Divider(),
                ListTile(
                  title: Text('TOTAL', style: TextStyle(fontWeight: FontWeight.bold)),
                  trailing: Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text('Required: ${total['required']}', 
                        style: TextStyle(fontWeight: FontWeight.bold)),
                      Text('Paid: ${total['paid']}', 
                        style: TextStyle(color: Colors.green, fontWeight: FontWeight.bold)),
                      Text('Balance: ${total['balance']}', 
                        style: TextStyle(
                          color: total['balance'] > 0 ? Colors.red : Colors.green,
                          fontWeight: FontWeight.bold,
                        )),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }
  
  Widget _buildStatusChip(String status) {
    Color color;
    switch (status) {
      case 'Paid':
        color = Colors.green;
        break;
      case 'Incomplete Payment':
      case 'Partial':
        color = Colors.orange;
        break;
      case 'Pending':
        color = Colors.red;
        break;
      default:
        color = Colors.grey;
    }
    
    return Chip(
      label: Text(status),
      backgroundColor: color.withOpacity(0.2),
      labelStyle: TextStyle(color: color, fontWeight: FontWeight.bold),
    );
  }
}
```

---

## Common Use Cases

### 1. Get All Payments for Current Year
```dart
final data = await api.getParentPayments(
  parentID: 4,
  schoolID: 3,
  year: '2024',
);
```

### 2. Get Payments for Specific Student
```dart
final data = await api.getParentPayments(
  parentID: 4,
  schoolID: 3,
  student: 12,
  year: '2024',
);
```

### 3. Filter by Fee Type
```dart
final data = await api.getParentPayments(
  parentID: 4,
  schoolID: 3,
  feeType: 'Tuition Fees',
  year: '2024',
);
```

### 4. Search Payments
```dart
final data = await api.getParentPayments(
  parentID: 4,
  schoolID: 3,
  search: 'John',
  year: '2024',
);
```

### 5. Get Fees Summary
```dart
final data = await api.getParentFeesSummary(
  parentID: 4,
  schoolID: 3,
  year: '2024',
);
```

---

## Notes

1. **Authentication:** You must login first to get `parentID` and `schoolID`
2. **Currency:** All amounts are in the local currency (TZS). Format them appropriately in your Flutter app
3. **Photo URLs:** Photo URLs are absolute URLs. Make sure your Flutter app can handle them
4. **Date Format:** All dates are in YYYY-MM-DD format
5. **Status Values:** Payment status can be "Pending", "Incomplete Payment", "Partial", or "Paid"
6. **Control Numbers:** Control numbers are used for payment references. They are unique identifiers for each payment
7. **Installments:** Installments are only shown if the fee allows installments and installments are configured
8. **Other Fees Details:** Only shown for "Other Fees" type, providing breakdown of miscellaneous fees
9. **Null Values:** Some fields may be null (e.g., photo, control_number, payment_date). Handle nulls appropriately

---

## Support

For issues or questions, please contact the API administrator or refer to the main API documentation.










