import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/account.dart';
import '../../state/auth_controller.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

/// Organization / branch switcher (scopes the catalog per company).
class OrganizationsScreen extends StatefulWidget {
  const OrganizationsScreen({super.key});

  @override
  State<OrganizationsScreen> createState() => _OrganizationsScreenState();
}

class _OrganizationsScreenState extends State<OrganizationsScreen> {
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<AuthController>().loadOrganizations();
    });
  }

  Future<void> _switch(Organization organization) async {
    setState(() => _busy = true);
    final auth = context.read<AuthController>();
    final ok = await auth.switchOrganization(organization.id);
    if (!mounted) return;
    setState(() => _busy = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(ok ? 'تم التبديل إلى ${organization.name}' : (auth.error ?? 'تعذّر التبديل')),
    ));
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthController>();
    final organizations = auth.organizations;

    return ScreenScaffold(
      title: 'الفروع والمنظمات',
      subtitle: 'اختر الجهة التي تتسوّق باسمها',
      child: RefreshIndicator(
        color: C.primary,
        onRefresh: auth.loadOrganizations,
        child: organizations.isEmpty
            ? ListView(
                children: const [
                  SizedBox(height: 60),
                  EmptyState(
                    icon: Icons.apartment_rounded,
                    title: 'لا توجد منظمات مرتبطة بحسابك',
                    message: 'تواصل مع الإدارة لربط حسابك بفرع أو شركة',
                  ),
                ],
              )
            : ListView.separated(
                padding: const EdgeInsets.only(bottom: 16),
                itemCount: organizations.length,
                separatorBuilder: (_, __) => const SizedBox(height: 10),
                itemBuilder: (context, index) {
                  final organization = organizations[index];
                  return NovaCard(
                    onTap: _busy || organization.isActive ? null : () => _switch(organization),
                    color: organization.isActive ? C.purpleSoft : Colors.white,
                    child: Row(
                      children: [
                        Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: organization.isActive ? C.primary : const Color(0xFFF1F5F9),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Icon(
                            Icons.apartment_rounded,
                            size: 18,
                            color: organization.isActive ? Colors.white : C.muted,
                          ),
                        ),
                        const SizedBox(width: 10),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(organization.name,
                                  maxLines: 1, overflow: TextOverflow.ellipsis,
                                  style: ts(12, weight: FontWeight.w700)),
                              const SizedBox(height: 2),
                              Text(
                                [
                                  'كود ${organization.code}',
                                  if (organization.type != null) organization.type!,
                                ].join(' · '),
                                style: ts(9, color: C.muted),
                              ),
                            ],
                          ),
                        ),
                        if (organization.isActive)
                          const Tag(text: 'الحالية', color: C.primaryDark, background: Colors.white)
                        else
                          const Icon(Icons.swap_horiz_rounded, size: 18, color: C.muted),
                      ],
                    ),
                  );
                },
              ),
      ),
    );
  }
}
