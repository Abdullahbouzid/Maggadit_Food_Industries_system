import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';

/// Saves an authenticated pdf download and opens it with the system viewer.
Future<bool> saveAndOpenPdf({
  required Uint8List bytes,
  required String fileName,
  BuildContext? context,
}) async {
  try {
    final dir = await getTemporaryDirectory();
    final file = File('${dir.path}/$fileName');
    await file.writeAsBytes(bytes, flush: true);
    final result = await OpenFilex.open(file.path);
    return result.type == ResultType.done;
  } catch (error) {
    debugPrint('[pdf] open failed: $error');
    return false;
  }
}

String pdfFileName(String prefix, String number) {
  final safe = number.replaceAll(RegExp(r'[^\w\-]'), '_');
  return '${prefix}_$safe.pdf';
}
