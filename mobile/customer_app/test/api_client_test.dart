import 'package:flutter_test/flutter_test.dart';
import 'package:maggadit_customer/core/api_client.dart';
import 'package:maggadit_customer/core/config.dart';

void main() {
  test('client resolves paths against the configured server', () {
    final api = ApiClient();
    expect(api.baseUrl, AppConfig.defaultApiBaseUrl);
    expect(api.requestBaseUrl, AppConfig.defaultApiBaseUrl);
    expect(api.host, 'blue-beaver-923812.hostingersite.com');
  });

  test('base URL is normalised whatever the caller passes', () {
    final api = ApiClient();
    for (final input in [
      'https://example.com',
      'https://example.com/',
      'https://example.com/api',
      'https://example.com/api/',
    ]) {
      api.baseUrl = input;
      expect(api.baseUrl, 'https://example.com/api', reason: input);
      expect(api.requestBaseUrl, 'https://example.com/api', reason: input);
    }
  });

  test('unreachable server reports the host instead of a bare failure', () async {
    // Port 9 is closed on the loopback interface, so this fails fast and for real.
    final api = ApiClient()..baseUrl = 'http://127.0.0.1:9';
    await expectLater(
      api.get('/customer/categories'),
      throwsA(
        isA<ApiException>()
            .having((e) => e.isNetwork, 'isNetwork', true)
            .having((e) => e.message, 'message', contains('127.0.0.1')),
      ),
    );
  });
}
