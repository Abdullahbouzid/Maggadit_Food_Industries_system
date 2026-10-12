class UserModel {
  final int id;
  final String name;
  final String email;
  final String username;
  final String? apiToken;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.username,
    this.apiToken,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      id: json['id'],
      name: json['name'],
      email: json['email'],
      username: json['username'],
      apiToken: json['api_token'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'username': username,
      'api_token': apiToken,
    };
  }
}