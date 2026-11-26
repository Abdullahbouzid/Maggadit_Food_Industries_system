import 'dart:convert';

import 'json_utils.dart';
import 'orders.dart';

class AppNotification {
  AppNotification({
    required this.id,
    required this.subject,
    required this.body,
    required this.iconType,
    required this.isRead,
    required this.isStarred,
    required this.isArchived,
    required this.createdAt,
    this.image,
  });

  factory AppNotification.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    return AppNotification(
      id: asInt(json['id']),
      subject: asString(json['subject']),
      body: asString(json['body']),
      iconType: asString(json['icon_type'], 'notification'),
      isRead: asBool(json['is_read']),
      isStarred: asBool(json['is_starred']),
      isArchived: asBool(json['is_archived']),
      createdAt: asString(json['created_at']),
      image: mediaUrl(asString(json['image']).isEmpty ? null : asString(json['image']),
          baseUrl: baseUrl),
    );
  }

  final int id;
  final String subject;
  final String body;
  final String iconType;
  final bool isRead;
  final bool isStarred;
  final bool isArchived;
  final String createdAt;
  final String? image;

  AppNotification copyWith({bool? isRead, bool? isStarred, bool? isArchived}) =>
      AppNotification(
        id: id,
        subject: subject,
        body: body,
        iconType: iconType,
        isRead: isRead ?? this.isRead,
        isStarred: isStarred ?? this.isStarred,
        isArchived: isArchived ?? this.isArchived,
        createdAt: createdAt,
        image: image,
      );
}

class Customer {
  Customer({
    required this.id,
    required this.name,
    required this.username,
    required this.email,
    this.activeOrganizationId,
  });

  factory Customer.fromJson(Map<String, dynamic> json) => Customer(
        id: asInt(json['id']),
        name: asString(json['name']),
        username: asString(json['username']),
        email: asString(json['email']),
        activeOrganizationId: json['active_organization_id'] == null
            ? null
            : asInt(json['active_organization_id']),
      );

  final int id;
  final String name;
  final String username;
  final String email;
  final int? activeOrganizationId;

  Map<String, dynamic> toJson() => {
        'id': id,
        'name': name,
        'username': username,
        'email': email,
        'active_organization_id': activeOrganizationId,
      };
}

class Organization {
  Organization({
    required this.id,
    required this.name,
    required this.code,
    required this.isActive,
    this.type,
    this.parentId,
  });

  factory Organization.fromJson(Map<String, dynamic> json) => Organization(
        id: asInt(json['id']),
        name: asString(json['name']),
        code: asString(json['code']),
        isActive: asBool(json['is_active']),
        type: asString(json['organization_type']).isEmpty
            ? null
            : asString(json['organization_type']),
        parentId: json['parent_id'] == null ? null : asInt(json['parent_id']),
      );

  final int id;
  final String name;
  final String code;
  final bool isActive;
  final String? type;
  final int? parentId;
}

class WalletTransaction {
  WalletTransaction({
    required this.id,
    required this.date,
    required this.label,
    required this.amount,
    required this.isDeposit,
    this.reference,
    this.method,
    this.notes,
    this.balanceAfter,
  });

  factory WalletTransaction.fromJson(Map<String, dynamic> json) {
    final direction = asString(json['direction']);
    final deposit = asDouble(json['deposit']) + asDouble(json['refund']);
    final debit = asDouble(json['purchase']) + asDouble(json['withdrawal']);
    final signed = asDouble(json['signed'], deposit - debit);
    final isDeposit = direction.isEmpty ? signed >= 0 : direction == 'credit';
    return WalletTransaction(
      id: asInt(json['id']),
      date: asString(json['date']),
      label: asString(json['label']),
      amount: asDouble(json['amount'], signed.abs()).abs(),
      isDeposit: isDeposit,
      reference: asString(json['reference']).isEmpty ? null : asString(json['reference']),
      method: asString(json['method']).isEmpty ? null : asString(json['method']),
      notes: asString(json['notes']).isEmpty ? null : asString(json['notes']),
      balanceAfter: json['balance_after'] == null ? null : asDouble(json['balance_after']),
    );
  }

  final int id;
  final String date;
  final String label;
  final double amount;
  final bool isDeposit;
  final String? reference;
  final String? method;
  final String? notes;
  final double? balanceAfter;
}

class WalletStatement {
  WalletStatement({
    required this.deposits,
    required this.debits,
    required this.balance,
    required this.invoiced,
    required this.paid,
    required this.transactions,
    this.outstanding = 0,
  });

  factory WalletStatement.fromJson(Map<String, dynamic> json) {
    final totals = asMap(json['totals']);
    final statement = json['statement'];
    final transactions = statement is List
        ? asMapList(statement)
        : asMapList(json['transactions']);
    return WalletStatement(
      deposits: asDouble(totals['deposits']),
      debits: asDouble(totals['debits']),
      balance: asDouble(totals['balance']),
      invoiced: asDouble(totals['invoiced']),
      paid: asDouble(totals['paid']),
      outstanding: asDouble(totals['outstanding']),
      transactions: transactions.map(WalletTransaction.fromJson).toList(),
    );
  }

  final double deposits;
  final double debits;
  final double balance;
  final double invoiced;
  final double paid;
  final double outstanding;
  final List<WalletTransaction> transactions;
}

class InvoiceSummary {
  InvoiceSummary({
    required this.id,
    required this.number,
    required this.date,
    required this.total,
    required this.status,
    required this.itemsCount,
    this.notes,
  });

  factory InvoiceSummary.fromJson(Map<String, dynamic> json) => InvoiceSummary(
        id: asInt(json['id']),
        number: asString(json['invoice_number']),
        date: asString(json['invoice_date']),
        total: asDouble(json['total_amount']),
        status: asString(json['status']),
        itemsCount: asInt(json['items_count']),
        notes: asString(json['notes']).isEmpty ? null : asString(json['notes']),
      );

  final int id;
  final String number;
  final String date;
  final double total;
  final String status;
  final int itemsCount;
  final String? notes;
}

class InvoiceDetail {
  InvoiceDetail({required this.invoice, required this.items});

  factory InvoiceDetail.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final data = asMap(json['data']);
    final items = asMapList(data['items']).map((e) {
      return OrderLine(
        name: asString(e['item_name_ar'] ?? e['item_name_en']),
        quantity: asDouble(e['quantity']),
        unitPrice: asDouble(e['unit_price']),
        lineTotal: asDouble(e['subtotal']),
        image: mediaUrl(asString(e['item_image']).isEmpty ? null : asString(e['item_image']),
            baseUrl: baseUrl),
      );
    }).toList();

    final invoice = InvoiceSummary.fromJson(data);
    return InvoiceDetail(
      invoice: items.isNotEmpty && invoice.itemsCount == 0
          ? InvoiceSummary(
              id: invoice.id,
              number: invoice.number,
              date: invoice.date,
              total: invoice.total,
              status: invoice.status,
              itemsCount: items.length,
              notes: invoice.notes,
            )
          : invoice,
      items: items,
    );
  }

  final InvoiceSummary invoice;
  final List<OrderLine> items;
}

class SurveySummary {
  SurveySummary({
    required this.id,
    required this.title,
    required this.description,
    required this.questionsCount,
    required this.estimatedMinutes,
    required this.isAnswered,
    this.startsAt,
    this.endsAt,
  });

  factory SurveySummary.fromJson(Map<String, dynamic> json) => SurveySummary(
        id: asInt(json['id']),
        title: asString(json['title']),
        description: asString(json['description']),
        questionsCount: asInt(json['questions_count']),
        estimatedMinutes: asInt(json['estimated_minutes']),
        isAnswered: asBool(json['is_answered']),
        startsAt: asString(json['starts_at']).isEmpty ? null : asString(json['starts_at']),
        endsAt: asString(json['ends_at']).isEmpty ? null : asString(json['ends_at']),
      );

  final int id;
  final String title;
  final String description;
  final int questionsCount;
  final int estimatedMinutes;
  final bool isAnswered;
  final String? startsAt;
  final String? endsAt;
}

class SurveyQuestion {
  SurveyQuestion({
    required this.id,
    required this.text,
    required this.type,
    required this.options,
    required this.isRequired,
    required this.sortOrder,
  });

  factory SurveyQuestion.fromJson(Map<String, dynamic> json) {
    var rawOptions = json['options'];
    if (rawOptions is String && rawOptions.trim().isNotEmpty) {
      try {
        rawOptions = jsonDecode(rawOptions);
      } catch (_) {
        rawOptions = null;
      }
    }
    final options = <String>[];
    if (rawOptions is List) {
      for (final option in rawOptions) {
        if (option is Map) {
          options.add(asString(option['label'] ?? option['text'] ?? option['value']));
        } else if (option != null) {
          options.add(option.toString());
        }
      }
    }
    return SurveyQuestion(
      id: asInt(json['id']),
      text: asString(json['question_text']),
      type: asString(json['question_type'], 'text'),
      options: options,
      isRequired: asBool(json['is_required']),
      sortOrder: asInt(json['sort_order']),
    );
  }

  final int id;
  final String text;
  final String type;
  final List<String> options;
  final bool isRequired;
  final int sortOrder;

  bool get isChoice => options.isNotEmpty;
}

class SurveyDetail {
  SurveyDetail({
    required this.id,
    required this.title,
    required this.description,
    required this.questions,
    required this.estimatedMinutes,
  });

  factory SurveyDetail.fromJson(Map<String, dynamic> json) {
    final survey = asMap(json['survey']);
    return SurveyDetail(
      id: asInt(survey['id'] ?? json['id']),
      title: asString(survey['title'] ?? json['title']),
      description: asString(survey['description'] ?? json['description']),
      estimatedMinutes: asInt(survey['estimated_minutes'] ?? json['estimated_minutes']),
      questions: asMapList(json['questions']).map(SurveyQuestion.fromJson).toList(),
    );
  }

  final int id;
  final String title;
  final String description;
  final List<SurveyQuestion> questions;
  final int estimatedMinutes;
}

class FaqItem {
  FaqItem({required this.question, required this.answer});

  factory FaqItem.fromJson(Map<String, dynamic> json) => FaqItem(
        question: asString(json['question'] ?? json['title']),
        answer: asString(json['answer'] ?? json['content'] ?? json['body']),
      );

  final String question;
  final String answer;
}

class ContactInfo {
  ContactInfo({
    this.phone,
    this.whatsapp,
    this.email,
    this.website,
    this.address,
    this.latitude,
    this.longitude,
  });

  factory ContactInfo.fromJson(Map<String, dynamic> json) {
    final data = asMap(json['data']).isEmpty ? json : asMap(json['data']);
    return ContactInfo(
      phone: asString(data['phone']).isEmpty ? null : asString(data['phone']),
      whatsapp: asString(data['whatsapp']).isEmpty ? null : asString(data['whatsapp']),
      email: asString(data['email']).isEmpty ? null : asString(data['email']),
      website: asString(data['website']).isEmpty ? null : asString(data['website']),
      address: asString(data['address']).isEmpty ? null : asString(data['address']),
      latitude: data['latitude'] == null ? null : asDouble(data['latitude']),
      longitude: data['longitude'] == null ? null : asDouble(data['longitude']),
    );
  }

  final String? phone;
  final String? whatsapp;
  final String? email;
  final String? website;
  final String? address;
  final double? latitude;
  final double? longitude;
}

class AboutContent {
  AboutContent({
    required this.title,
    required this.subtitle,
    required this.description,
    required this.vision,
    required this.mission,
    required this.values,
    this.image,
    this.coverImage,
  });

  factory AboutContent.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final content = asMap(json['content']).isEmpty ? json : asMap(json['content']);
    final values = content['values'];
    return AboutContent(
      title: asString(content['title']),
      subtitle: asString(content['subtitle']),
      description: asString(content['description']),
      vision: asString(content['vision']),
      mission: asString(content['mission']),
      values: values is List
          ? values.map((e) => e is Map ? asString(e['title'] ?? e['name'] ?? e) : asString(e)).toList()
          : const [],
      image: mediaUrl(asString(content['image_url'] ?? content['image']).isEmpty
          ? null
          : asString(content['image_url'] ?? content['image']), baseUrl: baseUrl),
      coverImage: mediaUrl(asString(content['cover_image_url'] ?? content['cover_image']).isEmpty
          ? null
          : asString(content['cover_image_url'] ?? content['cover_image']), baseUrl: baseUrl),
    );
  }

  final String title;
  final String subtitle;
  final String description;
  final String vision;
  final String mission;
  final List<String> values;
  final String? image;
  final String? coverImage;
}

class Branch {
  Branch({
    required this.id,
    required this.name,
    this.phone,
    this.address,
    this.isActive = true,
  });

  factory Branch.fromJson(Map<String, dynamic> json) => Branch(
        id: asInt(json['id']),
        name: asString(json['name']),
        phone: asString(json['phone']).isEmpty ? null : asString(json['phone']),
        address: asString(json['address']).isEmpty ? null : asString(json['address']),
        isActive: asBool(json['is_active'], true),
      );

  final int id;
  final String name;
  final String? phone;
  final String? address;
  final bool isActive;
}
