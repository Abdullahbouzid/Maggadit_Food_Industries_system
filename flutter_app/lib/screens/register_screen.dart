import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import '../services/api_service.dart';
import '../screens/login_screen.dart';
import '../screens/dashboard_screen.dart';

class RegisterScreen extends StatefulWidget {
  final String? initialLanguage;

  const RegisterScreen({Key? key, this.initialLanguage}) : super(key: key);

  @override
  _RegisterScreenState createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> with SingleTickerProviderStateMixin {
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _usernameController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _passwordController = TextEditingController();
  bool _isLoading = false;
  String _errorMessage = '';
  bool _showPassword = false;

  late AnimationController _animationController;
  late Animation<double> _fadeAnimation;
  late Animation<Offset> _slideAnimation;

  String _language = 'en';

  @override
  void initState() {
    super.initState();
    _animationController = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 800),
    );
    _fadeAnimation = Tween<double>(begin: 0.0, end: 1.0).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );
    _slideAnimation = Tween<Offset>(begin: const Offset(0, 0.3), end: Offset.zero).animate(
      CurvedAnimation(parent: _animationController, curve: Curves.easeOut),
    );
    _animationController.forward();
    
    _language = widget.initialLanguage ?? 'ar';
  }

  @override
  void dispose() {
    _animationController.dispose();
    _nameController.dispose();
    _usernameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    super.dispose();
  }

  void _showToast(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          message,
          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w500),
          textAlign: _language == 'ar' ? TextAlign.right : TextAlign.left,
        ),
        backgroundColor: isError ? const Color(0xFFDC2626) : const Color(0xFF1F2937),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(25)),
        padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
        margin: const EdgeInsets.only(bottom: 30, left: 20, right: 20),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final api = ApiService();
      final success = await api.register({
        'name': _nameController.text.trim(),
        'username': _usernameController.text.trim(),
        'email': _emailController.text.trim(),
        'password': _passwordController.text,
        'password_confirmation': _passwordController.text,
      });

      if (success) {
        // After registration, try to login
        final user = await api.login(
          _usernameController.text.trim(),
          _passwordController.text,
        );
        
        if (user != null) {
          final prefs = await SharedPreferences.getInstance();
          await prefs.setString('api_token', user.apiToken ?? '');
          await prefs.setString('customer_id', user.id.toString());
          await prefs.setString('user_name', user.name);
          await prefs.setString('user_email', user.email);
          await prefs.setString('user_username', user.username);
          await prefs.setString('customer_token', user.apiToken ?? '');
          await prefs.setString('lang', _language);

          if (!mounted) return;
          Navigator.of(context).pushReplacement(
            MaterialPageRoute(
              builder: (context) => DashboardScreen(
                token: user.apiToken ?? '',
                userId: user.id.toString(),
              ),
            ),
          );
          return;
        }
      }

      setState(() => _errorMessage = _language == 'ar' ? 'خطأ في إنشاء الحساب. تأكد من صحة البيانات.' : 'Error creating account. Try another username/email.');
      _showToast(_errorMessage, isError: true);
    } catch (e) {
      setState(() => _errorMessage = _language == 'ar' ? 'خطأ في الاتصال. يرجى المحاولة مرة أخرى.' : 'Connection error. Please try again.');
      _showToast(_errorMessage, isError: true);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _toggleLanguage() {
    setState(() {
      _language = _language == 'en' ? 'ar' : 'en';
    });
  }

  String get _brandTitle => _language == 'en' ? 'Create Account' : 'إنشاء حساب';
  String get _brandSub => _language == 'en' ? 'Maggadit Food Industries' : 'ماغاديت للصناعات الغذائية';
  String get _nameLabel => _language == 'en' ? 'Full Name' : 'الاسم الكامل';
  String get _namePlaceholder => _language == 'en' ? 'Enter your full name' : 'أدخل اسمك الكامل';
  String get _usernameLabel => _language == 'en' ? 'Username' : 'اسم المستخدم';
  String get _usernamePlaceholder => _language == 'en' ? 'Choose a username' : 'اختر اسم مستخدم';
  String get _emailLabel => _language == 'en' ? 'Email Address' : 'البريد الإلكتروني';
  String get _emailPlaceholder => _language == 'en' ? 'Enter your email' : 'أدخل بريدك الإلكتروني';
  String get _passwordLabel => _language == 'en' ? 'Password' : 'كلمة المرور';
  String get _passwordPlaceholder => _language == 'en' ? 'Create a password' : 'أنشئ كلمة مرور';
  String get _signupBtn => _language == 'en' ? 'Sign Up' : 'إنشاء الحساب';
  String get _loginText => _language == 'en' ? 'Already have an account?' : 'لديك حساب بالفعل؟';
  String get _loginLink => _language == 'en' ? 'Sign In' : 'تسجيل الدخول';
  String get _langSwitchText => _language == 'en' ? '🇸🇦 العربية' : '🇬🇧 English';
  String get _toastLoading => _language == 'en' ? '⏳ Creating account...' : '⏳ جاري إنشاء الحساب...';
  String get _toastError => _language == 'en' ? '❌ Error creating account. Try another username/email.' : '❌ خطأ في إنشاء الحساب. تأكد من صحة البيانات.';

  @override
  Widget build(BuildContext context) {
    final isRtl = _language == 'ar';
    
    return Directionality(
      textDirection: isRtl ? TextDirection.rtl : TextDirection.ltr,
      child: Scaffold(
        body: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
              colors: [Color(0xFFF5F7FC), Color(0xFFE9EEF4)],
            ),
          ),
          child: SafeArea(
            child: Stack(
              children: [
                Center(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        // Language switcher (top right)
                        Align(
                          alignment: isRtl ? Alignment.topLeft : Alignment.topRight,
                          child: Padding(
                            padding: const EdgeInsets.only(top: 30),
                            child: GestureDetector(
                              onTap: _toggleLanguage,
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                                decoration: BoxDecoration(
                                  color: Colors.white,
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: const Color(0xFFE2E8F0)),
                                  boxShadow: [
                                    BoxShadow(
                                      color: Colors.black.withOpacity(0.05),
                                      blurRadius: 8,
                                      offset: const Offset(0, 2),
                                    ),
                                  ],
                                ),
                                child: Text(
                                  _langSwitchText,
                                  style: const TextStyle(
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                    color: Color(0xFF9879b6),
                                  ),
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 30),

                        // Brand icon with logo
                        FadeTransition(
                          opacity: _fadeAnimation,
                          child: SlideTransition(
                            position: _slideAnimation,
                            child: Container(
                              width: 100,
                              height: 100,
                              decoration: BoxDecoration(
                                color: const Color(0xFF6B4C8A),
                                borderRadius: BorderRadius.circular(20),
                                boxShadow: [
                                  BoxShadow(
                                    color: const Color(0xFF6B4C8A).withOpacity(0.4),
                                    blurRadius: 20,
                                    spreadRadius: 5,
                                  ),
                                ],
                              ),
                              child: Center(
                                child: Image.asset(
                                  'assets/images/logo.png',
                                  width: 70,
                                  height: 70,
                                  errorBuilder: (context, error, stackTrace) {
                                    return const Icon(
                                      Icons.restaurant,
                                      size: 50,
                                      color: Colors.white,
                                    );
                                  },
                                ),
                              ),
                            ),
                          ),
                        ),
                        const SizedBox(height: 20),

                        // Title
                        FadeTransition(
                          opacity: _fadeAnimation,
                          child: Text(
                            _brandTitle,
                            style: const TextStyle(
                              fontSize: 28,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF1E293B),
                              letterSpacing: -0.5,
                            ),
                          ),
                        ),
                        const SizedBox(height: 4),
                        FadeTransition(
                          opacity: _fadeAnimation,
                          child: Text(
                            _brandSub,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.grey[500],
                            ),
                          ),
                        ),
                        const SizedBox(height: 16),

                        // Divider
                        FadeTransition(
                          opacity: _fadeAnimation,
                          child: Container(
                            height: 1,
                            color: const Color(0xFFE2E8F0),
                            width: double.infinity,
                          ),
                        ),
                        const SizedBox(height: 24),

                        // Register Card
                        SlideTransition(
                          position: _slideAnimation,
                          child: FadeTransition(
                            opacity: _fadeAnimation,
                            child: Container(
                              width: double.infinity,
                              padding: const EdgeInsets.all(28),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(32),
                                boxShadow: [
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.15),
                                    blurRadius: 35,
                                    spreadRadius: -12,
                                    offset: const Offset(0, 12),
                                  ),
                                  BoxShadow(
                                    color: Colors.black.withOpacity(0.02),
                                    blurRadius: 1,
                                    spreadRadius: 1,
                                  ),
                                ],
                              ),
                              child: Form(
                                key: _formKey,
                                child: Column(
                                  children: [
                                    // Error message
                                    if (_errorMessage.isNotEmpty) ...[
                                      Container(
                                        width: double.infinity,
                                        padding: const EdgeInsets.all(12),
                                        margin: const EdgeInsets.only(bottom: 16),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFFEF2F2),
                                          borderRadius: BorderRadius.circular(16),
                                          border: Border.all(color: const Color(0xFFEFC0C0)),
                                        ),
                                        child: Text(
                                          _errorMessage,
                                          style: const TextStyle(
                                            color: Color(0xFFDC2626),
                                            fontSize: 12,
                                            fontWeight: FontWeight.w500,
                                          ),
                                          textAlign: isRtl ? TextAlign.right : TextAlign.left,
                                        ),
                                      ),
                                    ],

                                    // Full Name field
                                    _buildInputField(
                                      icon: Icons.person_outline,
                                      label: _nameLabel,
                                      placeholder: _namePlaceholder,
                                      controller: _nameController,
                                      validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                          return _language == 'ar' ? 'الرجاء إدخال الاسم الكامل' : 'Please enter your full name';
                                        }
                                        return null;
                                      },
                                    ),
                                    const SizedBox(height: 16),

                                    // Username field
                                    _buildInputField(
                                      icon: Icons.person_outline,
                                      label: _usernameLabel,
                                      placeholder: _usernamePlaceholder,
                                      controller: _usernameController,
                                      validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                          return _language == 'ar' ? 'الرجاء اختيار اسم مستخدم' : 'Please choose a username';
                                        }
                                        return null;
                                      },
                                    ),
                                    const SizedBox(height: 16),

                                    // Email field
                                    _buildInputField(
                                      icon: Icons.email_outlined,
                                      label: _emailLabel,
                                      placeholder: _emailPlaceholder,
                                      controller: _emailController,
                                      keyboardType: TextInputType.emailAddress,
                                      validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                          return _language == 'ar' ? 'الرجاء إدخال البريد الإلكتروني' : 'Please enter your email';
                                        }
                                        if (!value.contains('@')) {
                                          return _language == 'ar' ? 'بريد إلكتروني غير صحيح' : 'Invalid email address';
                                        }
                                        return null;
                                      },
                                    ),
                                    const SizedBox(height: 16),

                                    // Password field
                                    _buildInputField(
                                      icon: Icons.lock_outline,
                                      label: _passwordLabel,
                                      placeholder: _passwordPlaceholder,
                                      controller: _passwordController,
                                      obscureText: !_showPassword,
                                      suffixIcon: IconButton(
                                        icon: Icon(
                                          _showPassword ? Icons.visibility_off : Icons.visibility,
                                          color: Colors.grey[400],
                                        ),
                                        onPressed: () {
                                          setState(() => _showPassword = !_showPassword);
                                        },
                                      ),
                                      validator: (value) {
                                        if (value == null || value.trim().isEmpty) {
                                          return _language == 'ar' ? 'الرجاء إدخال كلمة المرور' : 'Please enter a password';
                                        }
                                        if (value.length < 6) {
                                          return _language == 'ar' ? 'كلمة المرور يجب أن تكون 6 أحرف على الأقل' : 'Password must be at least 6 characters';
                                        }
                                        return null;
                                      },
                                    ),
                                    const SizedBox(height: 28),

                                    // Sign up button
                                    _isLoading
                                        ? Container(
                                            height: 56,
                                            decoration: BoxDecoration(
                                              borderRadius: BorderRadius.circular(40),
                                              color: const Color(0xFF9879b6).withOpacity(0.7),
                                            ),
                                            child: const Center(
                                              child: CircularProgressIndicator(
                                                valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                                                strokeWidth: 3,
                                              ),
                                            ),
                                          )
                                        : Container(
                                            width: double.infinity,
                                            height: 56,
                                            decoration: BoxDecoration(
                                              borderRadius: BorderRadius.circular(40),
                                              gradient: const LinearGradient(
                                                colors: [Color(0xFF9879b6), Color(0xFF6B4C8A)],
                                                begin: Alignment.centerLeft,
                                                end: Alignment.centerRight,
                                              ),
                                              boxShadow: [
                                                BoxShadow(
                                                  color: const Color(0xFF9879b6).withOpacity(0.3),
                                                  blurRadius: 10,
                                                  offset: const Offset(0, 4),
                                                ),
                                              ],
                                            ),
                                            child: Material(
                                              color: Colors.transparent,
                                              borderRadius: BorderRadius.circular(40),
                                              child: InkWell(
                                                onTap: _register,
                                                borderRadius: BorderRadius.circular(40),
                                                child: Center(
                                                  child: Text(
                                                    _signupBtn,
                                                    style: const TextStyle(
                                                      fontSize: 16,
                                                      fontWeight: FontWeight.w600,
                                                      color: Colors.white,
                                                      letterSpacing: 0.5,
                                                    ),
                                                  ),
                                                ),
                                              ),
                                            ),
                                          ),

                                    const SizedBox(height: 28),

                                    // Login link
                                    Row(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      children: [
                                        Text(
                                          _loginText,
                                          style: TextStyle(
                                            fontSize: 14,
                                            color: Colors.grey[500],
                                          ),
                                        ),
                                        TextButton(
                                          onPressed: () {
                                            Navigator.of(context).pushReplacement(
                                              MaterialPageRoute(
                                                builder: (context) => LoginScreen(initialLanguage: _language),
                                              ),
                                            );
                                          },
                                          child: Text(
                                            _loginLink,
                                            style: const TextStyle(
                                              fontSize: 14,
                                              fontWeight: FontWeight.w600,
                                              color: Color(0xFF9879b6),
                                              decoration: TextDecoration.none,
                                            ),
                                          ),
                                        ),
                                      ],
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInputField({
    required IconData icon,
    required String label,
    required String placeholder,
    required TextEditingController controller,
    bool obscureText = false,
    Widget? suffixIcon,
    TextInputType? keyboardType,
    String? Function(String?)? validator,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Colors.grey[700],
          ),
        ),
        const SizedBox(height: 8),
        TextFormField(
          controller: controller,
          obscureText: obscureText,
          validator: validator,
          keyboardType: keyboardType,
          textAlign: _language == 'ar' ? TextAlign.right : TextAlign.left,
          decoration: InputDecoration(
            hintText: placeholder,
            hintStyle: TextStyle(
              color: Colors.grey[400],
              fontSize: 14,
            ),
            prefixIcon: Icon(icon, color: const Color(0xFF9879b6), size: 22),
            suffixIcon: suffixIcon,
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
            filled: true,
            fillColor: const Color(0xFFF8FAFC),
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: const BorderSide(color: Color(0xFFE2E8F0), width: 1),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: const BorderSide(color: Color(0xFF9879b6), width: 2),
            ),
            errorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: const BorderSide(color: Color(0xFFDC2626), width: 1),
            ),
            focusedErrorBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(16),
              borderSide: const BorderSide(color: Color(0xFFDC2626), width: 2),
            ),
          ),
        ),
      ],
    );
  }
}