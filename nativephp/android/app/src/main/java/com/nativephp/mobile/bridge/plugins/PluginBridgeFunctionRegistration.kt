package com.nativephp.mobile.bridge.plugins

import androidx.fragment.app.FragmentActivity
import android.content.Context
import com.nativephp.mobile.bridge.BridgeFunctionRegistry
import com.nativephp.dialog.DialogFunctions

// AUTO-GENERATED FILE - DO NOT EDIT
// Generated from installed NativePHP plugins

fun registerPluginBridgeFunctions(activity: FragmentActivity, context: Context) {
    val registry = BridgeFunctionRegistry.shared



    // Plugin: nativephp/mobile-dialog
    registry.register("Dialog.Alert", DialogFunctions.Alert(activity))

    // Plugin: nativephp/mobile-dialog
    registry.register("Dialog.Toast", DialogFunctions.Toast(activity))
}

/**
 * Register only bridge functions that require Context (not Activity).
 * Used by WorkManager workers for cold-boot background execution
 * when no Activity is available.
 */
fun registerContextOnlyBridgeFunctions(context: Context) {
    val registry = BridgeFunctionRegistry.shared

    // No context-only bridge functions registered
}