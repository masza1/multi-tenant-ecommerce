# Toast Notification Usage Guide

## Overview
The application includes a floating toast notification system for displaying success, error, warning, and info messages. Toasts can be triggered from:
1. Server-side flash messages (automatic)
2. Client-side operations (manual)

## Automatic Toast from Flash Messages

Flash messages from the server are automatically converted to toasts. No manual work needed!

### Server-side (Controller):
```php
return redirect()->back()->with('success', 'Product added to cart!');
return redirect()->back()->with('error', 'Insufficient stock.');
return redirect()->back()->with('warning', 'Limited stock available.');
return redirect()->back()->with('info', 'Processing your request...');
```

### Client-side (Automatic):
The toast will appear automatically on the page!

## Manual Toast from Vue Component

### Basic Usage:

```vue
<template>
    <div>
        <button @click="handleAddToCart">Add to Cart</button>
    </div>
</template>

<script setup>
import { useFlashToast } from '@/composables/useFlashToast';

const { success, error, warning, info } = useFlashToast();

const handleAddToCart = async () => {
    try {
        // Your code here
        success('Product added to cart!');
    } catch (err) {
        error('Failed to add product. Please try again.');
    }
};
</script>
```

### Toast Methods:

```javascript
// Show success toast (5s duration)
success('Your message here');

// Show error toast (7s duration)
error('Your error message');

// Show warning toast (5s duration)
warning('Your warning message');

// Show info toast (5s duration)
info('Your info message');

// Custom duration (in milliseconds)
success('Your message', 3000); // 3 seconds
error('Your error', 10000);     // 10 seconds
```

## Toast Component

The Toast component is automatically included in the main layout via `MainLayout.vue`:

```vue
<script setup>
import { useFlashToast } from '@/composables/useFlashToast';
import Toast from '@/Components/Toast.vue';

const { toasts, removeToast } = useFlashToast();
</script>

<template>
    <Toast :toasts="toasts" @remove="removeToast" />
    <slot />
</template>
```

## Using MainLayout

Wrap your pages with MainLayout to get automatic toast support:

```vue
<script setup>
import MainLayout from '@/Layouts/MainLayout.vue';

defineProps({
    products: Object,
});
</script>

<template>
    <MainLayout>
        <!-- Your page content here -->
    </MainLayout>
</template>
```

## Toast Types and Styling

### Success (Green)
- Icon: CheckCircleIcon
- Background: Green-50
- Text: Green-800
- Duration: 5 seconds

### Error (Red)
- Icon: XCircleIcon
- Background: Red-50
- Text: Red-800
- Duration: 7 seconds (longer for attention)

### Warning (Yellow)
- Icon: ExclamationIcon
- Background: Yellow-50
- Text: Yellow-800
- Duration: 5 seconds

### Info (Blue)
- Icon: InformationCircleIcon
- Background: Blue-50
- Text: Blue-800
- Duration: 5 seconds

## Features

✅ **Auto-dismiss**: Toasts automatically close after their duration
✅ **Progress bar**: Visual indicator of remaining time
✅ **Closable**: Users can manually close any toast
✅ **Floating Position**: Fixed at top-right corner (non-blocking)
✅ **Smooth Animation**: Slides in from right with fade effect
✅ **Stack Support**: Multiple toasts can be displayed at once
✅ **Teleport**: Uses Vue 3 Teleport to render outside component tree

## Example: Form Submission with Toast

```vue
<template>
    <form @submit.prevent="submit">
        <Input v-model="form.email" label="Email" />
        <Button type="submit" :disabled="form.processing">
            {{ form.processing ? 'Submitting...' : 'Submit' }}
        </Button>
    </form>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';
import { useFlashToast } from '@/composables/useFlashToast';

const { success, error } = useFlashToast();
const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('some.route'), {
        onSuccess: () => {
            success('Form submitted successfully!');
        },
        onError: (errors) => {
            if (errors.email) {
                error(errors.email);
            } else {
                error('An error occurred. Please try again.');
            }
        },
    });
};
</script>
```

## Database Transactions with Toast

All critical operations now use database transactions:

- **Tenant Registration**: Creates tenant and domain atomically
- **User Registration**: Creates user account in transaction
- **Product Operations**: Create/update/delete in transactions
- **Cart Operations**: Add/update/remove in transactions

If any operation fails within a transaction, all changes are rolled back automatically, and an error toast is displayed to the user.

## Important Notes

1. Always use `useFlashToast()` instead of `useToast()` directly for automatic flash message handling
2. For form submissions with Inertia.js, flash messages will automatically show as toasts
3. Manual error handling should also trigger toasts via `error()` method
4. Keep toast messages concise and user-friendly
5. Use appropriate toast type (success, error, warning, info) for context
