import 'package:flutter/material.dart';

class SurveyScreen extends StatefulWidget {
  final String token;
  final String userId;

  const SurveyScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _SurveyScreenState createState() => _SurveyScreenState();
}

class _SurveyScreenState extends State<SurveyScreen> {
  final List<Map<String, dynamic>> _surveys = [];

  @override
  void initState() {
    super.initState();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Surveys'),
        centerTitle: true,
      ),
      body: _surveys.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Icon(
                    Icons.rate_review_outlined,
                    size: 64,
                    color: Colors.grey[400],
                  ),
                  SizedBox(height: 16),
                  Text(
                    'No surveys available',
                    style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.w500,
                    ),
                  ),
                  Text(
                    'Check back later for new surveys',
                    style: TextStyle(
                      fontSize: 14,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            )
          : ListView.builder(
              padding: EdgeInsets.all(16),
              itemCount: _surveys.length,
              itemBuilder: (context, index) {
                final survey = _surveys[index];
                return _SurveyCard(survey: survey);
              },
            ),
    );
  }
}

class _SurveyCard extends StatelessWidget {
  final Map<String, dynamic> survey;

  const _SurveyCard({required this.survey});

  @override
  Widget build(BuildContext context) {
    return Card(
      margin: EdgeInsets.only(bottom: 12),
      child: ListTile(
        leading: CircleAvatar(
          backgroundColor: Theme.of(context).primaryColor.withOpacity(0.1),
          child: Icon(Icons.rate_review, color: Theme.of(context).primaryColor),
        ),
        title: Text(survey['title'] ?? 'Survey'),
        subtitle: Text(
          survey['description'] ?? 'No description',
          maxLines: 1,
          overflow: TextOverflow.ellipsis,
        ),
        trailing: Icon(Icons.chevron_right, color: Colors.grey[400]),
        onTap: () {},
      ),
    );
  }
}