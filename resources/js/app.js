document.querySelectorAll('[data-password-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.getElementById(button.getAttribute('aria-controls'));
        const visible = input.type === 'password';
        input.type = visible ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(visible));
        button.setAttribute('aria-label', visible ? 'Hide password' : 'Show password');
        const showIcon = button.querySelector('[data-password-show]');
        const hideIcon = button.querySelector('[data-password-hide]');
        if (showIcon) showIcon.hidden = visible;
        if (hideIcon) hideIcon.hidden = !visible;
    });
});

document.querySelectorAll('[data-digits-only]').forEach((input) => {
    input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, Number(input.maxLength));
    });
});

document.querySelectorAll('[data-ph-mobile]').forEach((input) => {
    const error = input.closest('label')?.querySelector('[aria-live="polite"]');
    const validate = () => {
        const valid = /^09[0-9]{9}$/.test(input.value) && input.value.length === 11;
        const message = valid ? '' : input.title;
        input.setCustomValidity(message);
        input.setAttribute('aria-invalid', String(!valid));
        if (error) {
            error.textContent = message;
            error.hidden = valid;
        }
    };

    input.addEventListener('input', validate);
    input.addEventListener('blur', validate);
    input.addEventListener('invalid', validate);
    if (input.value !== '') validate();
});

document.addEventListener('click', (event) => {
    document.querySelectorAll('.rush-menu[open]').forEach((menu) => {
        if (!menu.contains(event.target)) menu.open = false;
    });
});

document.addEventListener('keydown', (event) => {
    if (event.key !== 'Escape') return;
    document.querySelectorAll('.rush-menu[open]').forEach((menu) => {
        menu.open = false;
        menu.querySelector('summary').focus();
    });
});

document.querySelectorAll('[data-menu-close]').forEach((button) => {
    button.addEventListener('click', () => {
        const menu = button.closest('details');
        menu?.removeAttribute('open');
        menu?.querySelector('summary')?.focus();
    });
});

document.querySelectorAll('[data-multistep-form]').forEach((form) => {
    const panels = [...form.querySelectorAll('[data-step-panel]')];
    const indicators = [...form.querySelectorAll('[data-step-indicator]')];
    const previous = form.querySelector('[data-step-previous]');
    const next = form.querySelector('[data-step-next]');
    const submit = form.querySelector('[data-step-submit]');
    let step = Number(form.dataset.initialStep ?? 0);

    const render = () => {
        panels.forEach((panel, index) => { panel.hidden = index !== step; });
        indicators.forEach((indicator, index) => {
            indicator.classList.toggle('bg-lime-50', index <= step);
            indicator.querySelector('span').classList.toggle('bg-lime-400', index <= step);
        });
        previous.hidden = step === 0;
        next.hidden = step === panels.length - 1;
        submit.hidden = step !== panels.length - 1;
    };

    next.addEventListener('click', () => {
        const fields = [...panels[step].querySelectorAll('input, select, textarea')];
        if (fields.some((field) => !field.reportValidity())) return;
        const availabilityFields = fields.filter((field) => field.name === 'availability_days[]');
        if (availabilityFields.length > 0 && !availabilityFields.some((field) => field.checked)) {
            availabilityFields[0].setCustomValidity('Select at least one available day.');
            availabilityFields[0].reportValidity();
            availabilityFields[0].addEventListener('change', () => availabilityFields[0].setCustomValidity(''), { once: true });
            return;
        }
        step = Math.min(step + 1, panels.length - 1);
        render();
    });
    previous.addEventListener('click', () => { step = Math.max(step - 1, 0); render(); });
    render();
});

document.querySelectorAll('input[name="payment_method"]').forEach((input) => {
    input.addEventListener('change', () => {
        const fields = document.querySelector('[data-gcash-fields]');
        if (!fields) return;
        const isGcash = document.querySelector('input[name="payment_method"]:checked')?.value === 'gcash';
        fields.hidden = !isGcash;
        fields.querySelectorAll('input').forEach((field) => { field.required = isGcash; });
    });
    if (input.checked) input.dispatchEvent(new Event('change'));
});

document.querySelectorAll('[data-chat-panel-target]').forEach((button) => {
    button.addEventListener('click', () => {
        const targetName = button.dataset.chatPanelTarget;
        const target = document.querySelector(`[data-chat-panel="${targetName}"]`);
        if (!target) return;

        const shouldOpen = target.hidden;
        document.querySelectorAll('[data-chat-panel]').forEach((panel) => { panel.hidden = true; });
        target.hidden = !shouldOpen;
        button.closest('details')?.removeAttribute('open');

        if (shouldOpen) target.querySelector('input, button, audio, a')?.focus();
    });
});

document.querySelectorAll('[data-chat-panel-close]').forEach((button) => {
    button.addEventListener('click', () => {
        button.closest('[data-chat-panel]').hidden = true;
    });
});

document.querySelectorAll('[data-chat-messages]').forEach((messages) => {
    messages.scrollTop = messages.scrollHeight;
});

document.querySelectorAll('[data-chat-compose]').forEach((form) => {
    const attachment = form.querySelector('[data-chat-attachment]');
    const photoButton = form.querySelector('[data-chat-photo]');
    const cameraButton = form.querySelector('[data-chat-camera]');
    const cameraDialog = form.querySelector('[data-chat-camera-dialog]');
    const cameraVideo = form.querySelector('[data-chat-camera-video]');
    const cameraCanvas = form.querySelector('[data-chat-camera-canvas]');
    const cameraStatus = form.querySelector('[data-chat-camera-status]');
    const cameraCapture = form.querySelector('[data-chat-camera-capture]');
    const cameraClose = form.querySelector('[data-chat-camera-close]');
    const cameraCancel = form.querySelector('[data-chat-camera-cancel]');
    const photoPreview = form.querySelector('[data-chat-photo-preview]');
    const clearButton = form.querySelector('[data-chat-attachment-clear]');
    const status = form.querySelector('[data-chat-attachment-status]');
    const message = form.querySelector('textarea[name="body"]');
    let cameraStream = null;
    let previewUrl = null;

    const setStatus = (text, hasAttachment = false) => {
        status.textContent = text;
        clearButton.hidden = !hasAttachment;
    };

    const clearPreview = () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = null;
        photoPreview.removeAttribute('src');
        photoPreview.hidden = true;
    };

    const previewPhoto = (file) => {
        clearPreview();
        previewUrl = URL.createObjectURL(file);
        photoPreview.src = previewUrl;
        photoPreview.hidden = false;
    };

    const choosePhoto = (capture = null) => {
        attachment.value = '';
        attachment.accept = 'image/jpeg,image/png,image/gif,image/webp';
        if (capture) attachment.setAttribute('capture', capture);
        else attachment.removeAttribute('capture');
        attachment.click();
    };

    const stopCamera = () => {
        cameraStream?.getTracks().forEach((track) => track.stop());
        cameraStream = null;
        cameraVideo.srcObject = null;
    };

    const closeCamera = () => {
        stopCamera();
        if (cameraDialog.open) cameraDialog.close();
    };

    photoButton.addEventListener('click', () => choosePhoto());

    attachment.addEventListener('change', () => {
        const file = attachment.files?.[0];
        setStatus(file ? file.name : 'Attach or take a photo (up to 2 MB).', Boolean(file));
        if (file) previewPhoto(file);
        else clearPreview();
        message.setCustomValidity('');
    });

    clearButton.addEventListener('click', () => {
        attachment.value = '';
        clearPreview();
        setStatus('Attach or take a photo (up to 2 MB).');
    });

    cameraButton.addEventListener('click', async () => {
        if (!navigator.mediaDevices?.getUserMedia || !window.DataTransfer || !cameraDialog.showModal) {
            choosePhoto('environment');
            return;
        }

        try {
            cameraStatus.textContent = 'Starting camera…';
            cameraDialog.showModal();
            cameraStream = await navigator.mediaDevices.getUserMedia({
                video: { facingMode: { ideal: 'environment' } },
                audio: false,
            });
            cameraVideo.srcObject = cameraStream;
            await cameraVideo.play();
            cameraStatus.textContent = 'Camera ready. Frame your photo and select Capture photo.';
        } catch {
            closeCamera();
            setStatus('Live camera access was unavailable. Opening the device camera instead.');
            choosePhoto('environment');
        }
    });

    cameraCapture.addEventListener('click', () => {
        if (!cameraVideo.videoWidth || !cameraVideo.videoHeight) {
            cameraStatus.textContent = 'The camera is still loading. Try again in a moment.';
            return;
        }

        const scale = Math.min(1, 1280 / cameraVideo.videoWidth);
        cameraCanvas.width = Math.round(cameraVideo.videoWidth * scale);
        cameraCanvas.height = Math.round(cameraVideo.videoHeight * scale);
        cameraCanvas.getContext('2d').drawImage(cameraVideo, 0, 0, cameraCanvas.width, cameraCanvas.height);
        cameraCanvas.toBlob((blob) => {
            if (!blob) {
                cameraStatus.textContent = 'The photo could not be captured. Please try again.';
                return;
            }

            const file = new File([blob], `camera-photo-${Date.now()}.jpg`, { type: 'image/jpeg' });
            const transfer = new DataTransfer();
            transfer.items.add(file);
            attachment.files = transfer.files;
            attachment.removeAttribute('capture');
            previewPhoto(file);
            setStatus('Camera photo ready to send.', true);
            message.setCustomValidity('');
            closeCamera();
        }, 'image/jpeg', 0.82);
    });

    cameraClose.addEventListener('click', closeCamera);
    cameraCancel.addEventListener('click', closeCamera);
    cameraDialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeCamera();
    });

    form.addEventListener('submit', (event) => {
        const hasContent = message.value.trim() !== '' || Boolean(attachment.files?.length);
        message.setCustomValidity(hasContent ? '' : 'Write a message or attach a photo.');
        if (!hasContent) {
            event.preventDefault();
            message.reportValidity();
        }
    });
});

const memberList = document.querySelector('[data-member-list]');
const memberTotal = document.querySelector('[data-member-total]');

if (memberList) {
    let isRefreshing = false;

    const refreshMembers = async () => {
        if (isRefreshing || document.visibilityState !== 'visible') return;

        isRefreshing = true;

        try {
            const response = await fetch(window.location.href, {
                cache: 'no-store',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            if (!response.ok) return;

            const documentCopy = new DOMParser().parseFromString(await response.text(), 'text/html');
            const updatedMemberList = documentCopy.querySelector('[data-member-list]');
            const updatedMemberTotal = documentCopy.querySelector('[data-member-total]');

            if (updatedMemberList) memberList.innerHTML = updatedMemberList.innerHTML;
            if (updatedMemberTotal && memberTotal) memberTotal.textContent = updatedMemberTotal.textContent;
        } catch {
            // Keep the current directory visible when a background refresh cannot connect.
        } finally {
            isRefreshing = false;
        }
    };

    window.setInterval(refreshMembers, 5000);
    document.addEventListener('visibilitychange', refreshMembers);
}

document.querySelectorAll('[data-attendance-calendar-dialog]').forEach((dialog) => {
    const attendanceSection = dialog.closest('section');
    const openButton = attendanceSection?.querySelector('[data-attendance-calendar-open]');
    const closeButtons = [...dialog.querySelectorAll('[data-attendance-calendar-close]')];
    const dateButtons = [...dialog.querySelectorAll('[data-attendance-date]')];
    const dateInput = dialog.querySelector('[data-attendance-date-input]');

    const closeCalendar = () => dialog.close();
    const openCalendar = () => {
        if (!dialog.open) dialog.showModal();
    };

    dateButtons.forEach((button) => {
        button.addEventListener('click', () => {
            dateButtons.forEach((dateButton) => {
                dateButton.setAttribute('aria-pressed', 'false');
                dateButton.classList.remove('border-lime-400', 'bg-lime-50', 'ring-2', 'ring-lime-100');
                dateButton.classList.add('border-zinc-100', 'bg-zinc-50');
            });
            button.setAttribute('aria-pressed', 'true');
            button.classList.remove('border-zinc-100', 'bg-zinc-50');
            button.classList.add('border-lime-400', 'bg-lime-50', 'ring-2', 'ring-lime-100');
            dateInput.value = button.dataset.attendanceDate;
        });
    });

    openButton?.addEventListener('click', openCalendar);
    closeButtons.forEach((button) => button.addEventListener('click', closeCalendar));
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeCalendar();
    });

    if (new URLSearchParams(window.location.search).has('attendance_calendar')) {
        openCalendar();
    }
});

document.querySelectorAll('[data-coach-calendar]').forEach((calendar) => {
    const dialog = calendar.querySelector('[data-coach-schedule-dialog]');
    const dateButtons = [...calendar.querySelectorAll('[data-coach-calendar-date]')];
    const schedulePanels = [...dialog.querySelectorAll('[data-coach-schedule-panel]')];
    const closeButtons = [...dialog.querySelectorAll('[data-coach-schedule-close]')];

    const closeSchedule = () => dialog.close();

    dateButtons.forEach((button) => {
        button.addEventListener('click', () => {
            schedulePanels.forEach((panel) => {
                panel.hidden = panel.dataset.coachSchedulePanel !== button.dataset.coachCalendarDate;
            });
            dialog.showModal();
        });
    });

    closeButtons.forEach((button) => button.addEventListener('click', closeSchedule));
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeSchedule();
    });
});

document.querySelectorAll('[data-attendance-toast]').forEach((toast) => {
    const title = toast.querySelector('[data-attendance-toast-title]');
    const body = toast.querySelector('[data-attendance-toast-body]');
    const closeButton = toast.querySelector('[data-attendance-toast-close]');
    const knownNotificationIds = new Set(JSON.parse(toast.dataset.knownNotificationIds ?? '[]'));
    let hideTimer = null;

    const hideToast = () => {
        toast.classList.add('translate-y-3', 'opacity-0');
        toast.classList.remove('translate-y-0', 'opacity-100');
        window.setTimeout(() => { toast.hidden = true; }, 300);
    };

    const showToast = (notification) => {
        window.clearTimeout(hideTimer);
        title.textContent = notification.title;
        body.textContent = notification.body;
        toast.hidden = false;
        window.requestAnimationFrame(() => {
            toast.classList.remove('translate-y-3', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });
        hideTimer = window.setTimeout(hideToast, 5000);
    };

    const pollAttendanceNotifications = async () => {
        if (document.visibilityState !== 'visible') return;

        try {
            const response = await fetch(toast.dataset.notificationUrl, {
                cache: 'no-store',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) return;

            const payload = await response.json();
            const notifications = payload.notifications ?? [];

            const newNotification = notifications.find((notification) => !knownNotificationIds.has(notification.id));
            notifications.forEach((notification) => knownNotificationIds.add(notification.id));
            if (newNotification) showToast(newNotification);
        } catch {
            // Keep the portal usable if notification polling is temporarily unavailable.
        }
    };

    closeButton.addEventListener('click', hideToast);
    pollAttendanceNotifications();
    window.setInterval(pollAttendanceNotifications, 2000);
    document.addEventListener('visibilitychange', pollAttendanceNotifications);
});

let attendanceQrReaderPromise;

const getAttendanceQrReader = () => {
    attendanceQrReaderPromise ??= import('@zxing/browser').then(({ BrowserQRCodeReader }) => (
        new BrowserQRCodeReader(undefined, { delayBetweenScanAttempts: 250 })
    ));

    return attendanceQrReaderPromise;
};

document.querySelectorAll('[data-attendance-scanner]').forEach((scanner) => {
    const openButton = scanner.querySelector('[data-attendance-scan-open]');
    const dialog = scanner.querySelector('[data-attendance-scan-dialog]');
    const video = scanner.querySelector('[data-attendance-scan-video]');
    const closeButton = scanner.querySelector('[data-attendance-scan-close]');
    const cancelButton = scanner.querySelector('[data-attendance-scan-cancel]');
    const fileInput = scanner.querySelector('[data-attendance-scan-file]');
    const cameraFallbackButton = scanner.querySelector('[data-attendance-camera-fallback]');
    const message = scanner.querySelector('[data-attendance-scan-message]');
    const status = scanner.querySelector('[data-attendance-status]');
    let controls = null;
    let isSubmitting = false;
    const canUseLiveCamera = window.isSecureContext && Boolean(navigator.mediaDevices?.getUserMedia);

    const stopScanner = () => {
        controls?.stop();
        controls = null;
        video.srcObject?.getTracks().forEach((track) => track.stop());
        video.srcObject = null;
    };

    const closeScanner = () => {
        stopScanner();
        if (dialog.open) dialog.close();
    };

    const submitQrValue = async (qrValue) => {
        if (isSubmitting) return;

        isSubmitting = true;
        stopScanner();
        message.textContent = 'QR detected. Verifying membership…';

        try {
            const response = await fetch(scanner.dataset.checkInUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': scanner.dataset.csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ qr_value: qrValue }),
            });
            const payload = await response.json();

            if (!response.ok) {
                throw new Error(payload.message ?? 'The QR code could not be verified.');
            }

            message.textContent = payload.message;
            status.textContent = payload.message;
            status.classList.remove('text-zinc-300');
            status.classList.add('border-lime-700', 'text-lime-300');
            window.setTimeout(() => window.location.reload(), 900);
        } catch (error) {
            const errorMessage = error instanceof Error ? error.message : 'The QR code could not be verified.';
            message.textContent = errorMessage;
            status.textContent = errorMessage;
            status.classList.remove('text-zinc-300');
            status.classList.add('border-red-800', 'text-red-300');
            isSubmitting = false;
        }
    };

    const startCamera = async () => {
        message.textContent = 'Starting camera…';
        cameraFallbackButton.hidden = true;

        if (!canUseLiveCamera) {
            cameraFallbackButton.hidden = false;
            message.textContent = 'Opening the phone camera. Photograph the QR and it will be checked automatically.';
            return;
        }

        try {
            const reader = await getAttendanceQrReader();
            controls = await reader.decodeFromConstraints({
                audio: false,
                video: {
                    facingMode: { ideal: 'environment' },
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                },
            }, video, (result) => {
                if (result) submitQrValue(result.getText());
            });
            message.textContent = 'Camera ready. Place the entire QR inside the green rectangle—it will scan automatically.';
        } catch (error) {
            const cameraErrors = {
                NotAllowedError: 'Camera permission is blocked. Allow camera access in the browser site settings, then reopen the scanner.',
                NotFoundError: 'No camera was detected on this device. Connect or enable a webcam, or use the device camera / QR image button below.',
                NotReadableError: 'The camera is already in use by another application. Close that application, then reopen the scanner.',
                OverconstrainedError: 'The connected camera does not support the requested video mode. Use the device camera / QR image button below.',
                SecurityError: 'The browser blocked camera access for this page. Allow camera access in the site settings.',
            };
            message.textContent = cameraErrors[error?.name] ?? `Camera could not start${error?.message ? `: ${error.message}` : '.'}`;
            cameraFallbackButton.hidden = false;
        }
    };

    openButton.addEventListener('click', () => {
        isSubmitting = false;
        dialog.showModal();
        startCamera();

        if (!canUseLiveCamera) fileInput.click();
    });

    cameraFallbackButton.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files?.[0];
        if (!file) return;

        if (!dialog.open) dialog.showModal();
        stopScanner();
        message.textContent = 'Reading QR image…';
        const imageUrl = URL.createObjectURL(file);

        try {
            const reader = await getAttendanceQrReader();
            const result = await reader.decodeFromImageUrl(imageUrl);
            await submitQrValue(result.getText());
        } catch {
            message.textContent = 'No readable QR code was found in that image.';
        } finally {
            URL.revokeObjectURL(imageUrl);
            fileInput.value = '';
        }
    });

    closeButton.addEventListener('click', closeScanner);
    cancelButton.addEventListener('click', closeScanner);
    dialog.addEventListener('cancel', (event) => {
        event.preventDefault();
        closeScanner();
    });
    dialog.addEventListener('close', stopScanner);
});
