package com.maggadit.foodindustries.firebase

import android.app.NotificationChannel
import android.app.NotificationManager
import android.content.Context
import android.content.Intent
import android.os.Build
import android.util.Log
import android.webkit.WebView
import androidx.core.app.NotificationCompat
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage
import com.nativephp.mobile.security.LaravelCookieStore
import com.nativephp.mobile.ui.MainActivity

class MyFirebaseMessagingService : FirebaseMessagingService() {

    companion object {
        const val CHANNEL_ID = "maggadit_channel"
        const val TAG = "MaggaditFCM"
        fun sendTokenToBackendStatic(token: String, context: Context) {
            val service = MyFirebaseMessagingService()
            service.sendTokenToBackend(token)
        }
    }

    override fun onNewToken(token: String) {
        super.onNewToken(token)
        Log.d(TAG, "FCM Token: $token")

        // احفظ في SharedPreferences
        val prefs = getSharedPreferences("maggadit_prefs", Context.MODE_PRIVATE)
        prefs.edit().putString("fcm_token", token).apply()

        // اكتب إلى localStorage في WebView فوراً بحيث يكون متاحاً عند تسجيل الدخول
        val webView = MainActivity.instance?.getWebView()
        if (webView != null) {
            webView.post {
                webView.evaluateJavascript("localStorage.setItem('fcm_token', '${token.replace("\\", "\\\\").replace("'", "\\'")}'); console.log('✅ FCM token saved to localStorage:', '${token.replace("'", "\\'").substring(0, Math.min(20, token.length))}...');", null)
                Log.d(TAG, "📱 FCM token written to WebView localStorage")
            }
        } else {
            Log.w(TAG, "⚠️ WebView not available yet, FCM token stored in SharedPreferences for later sync")
        }

        sendTokenToBackend(token)
    }

    override fun onMessageReceived(remoteMessage: RemoteMessage) {
        super.onMessageReceived(remoteMessage)
        Log.d(TAG, "From: ${remoteMessage.from ?: "unknown"}")

        val notification = remoteMessage.notification
        val title = notification?.title ?: (remoteMessage.data["title"] ?: "Maggadit")
        val body = notification?.body ?: (remoteMessage.data["body"] ?: "Notification")

        Log.d(TAG, "Title: $title, Body: $body")
        showNotification(title, body)
    }

    private fun showNotification(title: String, body: String) {
        val notificationManager = getSystemService(Context.NOTIFICATION_SERVICE) as NotificationManager

        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.O) {
            val channel = NotificationChannel(
                CHANNEL_ID,
                "Maggadit Notifications",
                NotificationManager.IMPORTANCE_HIGH
            ).apply {
                description = "إشعارات طلبات العملاء والعروض"
                enableVibration(true)
                enableLights(true)
            }
            notificationManager.createNotificationChannel(channel)
        }

        val intent = Intent(this, com.nativephp.mobile.ui.MainActivity::class.java)
        val pendingIntent = android.app.PendingIntent.getActivity(
            this,
            0,
            intent,
            android.app.PendingIntent.FLAG_IMMUTABLE or android.app.PendingIntent.FLAG_UPDATE_CURRENT
        )

        val builder = NotificationCompat.Builder(this, CHANNEL_ID)
            .setSmallIcon(android.R.drawable.ic_dialog_info)
            .setContentTitle(title)
            .setContentText(body)
            .setPriority(NotificationCompat.PRIORITY_HIGH)
            .setAutoCancel(true)
            .setContentIntent(pendingIntent)
            .setDefaults(NotificationCompat.DEFAULT_ALL)

        notificationManager.notify(System.currentTimeMillis().toInt(), builder.build())
    }

    private fun sendTokenToBackend(token: String) {
        val prefs = getSharedPreferences("maggadit_prefs", Context.MODE_PRIVATE)
        prefs.edit().putString("fcm_token", token).apply()

        // Try Bearer token first (for API auth)
        val authToken = prefs.getString("api_token", "") ?: ""
        if (authToken.isNotEmpty()) {
            sendTokenToApiWithBearer(token, authToken)
            return
        }

        // Fallback to cookie auth (for web session)
        val cookieHeader = LaravelCookieStore.asCookieHeader()
        if (cookieHeader.isNotEmpty()) {
            sendTokenToApiWithCookie(token, cookieHeader)
        } else {
            Log.w(TAG, "No auth token or cookie found, FCM token stored locally for later sync")
        }
    }

    private fun sendTokenToApiWithCookie(token: String, cookieHeader: String) {
        Thread {
            try {
                // Use local Laravel API endpoint (http://127.0.0.1/api/customer/fcm-token)
                val url = java.net.URL("http://127.0.0.1/api/customer/fcm-token")
                val connection = url.openConnection() as java.net.HttpURLConnection
                connection.requestMethod = "POST"
                connection.doOutput = true
                connection.setRequestProperty("Cookie", cookieHeader)
                connection.setRequestProperty("Content-Type", "application/json")
                connection.setRequestProperty("Accept", "application/json")
                connection.setRequestProperty("X-Requested-With", "XMLHttpRequest")

                val json = "{\"fcm_token\":\"$token\"}"
                connection.outputStream.write(json.toByteArray())
                connection.outputStream.flush()
                connection.outputStream.close()

                val responseCode = connection.responseCode
                Log.d(TAG, "FCM Token sent to local API: $responseCode")
                
                if (responseCode != 200 && responseCode != 201) {
                    val errorStream = connection.errorStream
                    if (errorStream != null) {
                        val error = errorStream.bufferedReader().readText()
                        Log.e(TAG, "FCM Token API error response: $error")
                    }
                }
            } catch (e: Exception) {
                Log.e(TAG, "Failed to send FCM token to local API: ${e.message}")
            }
        }.start()
    }

    private fun sendTokenToApiWithBearer(token: String, authToken: String) {
        Thread {
            try {
                // Use local Laravel API endpoint (http://127.0.0.1/api/customer/fcm-token)
                val url = java.net.URL("http://127.0.0.1/api/customer/fcm-token")
                val connection = url.openConnection() as java.net.HttpURLConnection
                connection.requestMethod = "POST"
                connection.doOutput = true
                connection.setRequestProperty("Authorization", "Bearer $authToken")
                connection.setRequestProperty("Content-Type", "application/json")
                connection.setRequestProperty("Accept", "application/json")

                val json = "{\"fcm_token\":\"$token\"}"
                connection.outputStream.write(json.toByteArray())
                connection.outputStream.flush()
                connection.outputStream.close()

                val responseCode = connection.responseCode
                Log.d(TAG, "FCM Token sent to local API (Bearer): $responseCode")
                
                if (responseCode == 200 || responseCode == 201) {
                    Log.d(TAG, "✅ FCM Token saved successfully!")
                } else {
                    val errorStream = connection.errorStream
                    if (errorStream != null) {
                        val error = errorStream.bufferedReader().readText()
                        Log.e(TAG, "FCM Token API error response: $error")
                    }
                }
            } catch (e: Exception) {
                Log.e(TAG, "Failed to send FCM token to local API (Bearer): ${e.message}")
            }
        }.start()
    }
}