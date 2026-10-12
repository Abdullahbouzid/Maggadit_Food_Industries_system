class NotificationModel {
  final int id;
  final String subject;
  final String body;
  final String iconType;
  final String createdAt;
  final bool isRead;

  NotificationModel({
    required this.id,
    required this.subject,
    required this.body,
    required this.iconType,
    required this.createdAt,
    required this.isRead,
  });

  factory NotificationModel.fromJson(Map<String, dynamic> json) {
    return NotificationModel(
      id: json['id'],
      subject: json['subject'] ?? '',
      body: json['body'] ?? '',
      iconType: json['icon_type'] ?? 'notification',
      createdAt: json['created_at'] ?? '',
      isRead: json['is_read'] ?? false,
    );
  }
}