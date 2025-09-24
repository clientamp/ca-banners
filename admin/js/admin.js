/**
 * CA Banners Admin JavaScript
 */

jQuery(document).ready(function($) {
    'use strict';
    
    // Quick options functionality
    function updateQuickOptions() {
        // Handle include quick options
        const includeOptions = document.querySelectorAll('.ca-banner-quick-option');
        const includeTextarea = document.getElementById('banner_urls');
        
        includeOptions.forEach(function(option) {
            option.addEventListener('change', function() {
                updateTextareaFromQuickOptions(includeTextarea, includeOptions, 'include');
            });
        });
        
        // Handle exclude quick options
        const excludeOptions = document.querySelectorAll('.ca-banner-quick-exclude');
        const excludeTextarea = document.getElementById('banner_exclude_urls');
        
        excludeOptions.forEach(function(option) {
            option.addEventListener('change', function() {
                updateTextareaFromQuickOptions(excludeTextarea, excludeOptions, 'exclude');
            });
        });
    }
    
    function updateTextareaFromQuickOptions(textarea, options, type) {
        if (!textarea) return;
        
        let currentUrls = textarea.value.split('\n').filter(url => url.trim() !== '');
        let quickValues = [];
        
        // Get selected quick options
        options.forEach(function(option) {
            if (option.checked) {
                quickValues.push(option.getAttribute('data-value'));
            }
        });
        
        // Remove existing quick option values
        currentUrls = currentUrls.filter(url => {
            return !options.some(option => url.trim() === option.getAttribute('data-value'));
        });
        
        // Add new quick option values
        quickValues.forEach(function(value) {
            if (!currentUrls.includes(value)) {
                currentUrls.push(value);
            }
        });
        
        // Update textarea
        textarea.value = currentUrls.join('\n');
        
        // Trigger change event for preview
        textarea.dispatchEvent(new Event('change'));
    }
    
    // Initialize quick options
    updateQuickOptions();
    
// Conditional field logic
function updateConditionalFields() {
    const sitewideYes = document.getElementById('banner_sitewide_yes');
    const sitewideNo = document.getElementById('banner_sitewide_no');
    const conditionalFields = document.querySelectorAll('.ca-banner-conditional-field[data-depends-on="banner_sitewide"]');
    
    if ((sitewideYes || sitewideNo) && conditionalFields.length > 0) {
        // Ensure at least one radio button is selected (default to sitewide = true)
        if (!sitewideYes.checked && !sitewideNo.checked) {
            sitewideYes.checked = true;
        }
        
        const isSitewide = sitewideYes ? sitewideYes.checked : false;
        
        conditionalFields.forEach(function(field) {
            const showWhen = field.getAttribute('data-show-when');
            const shouldShow = (showWhen === 'true' && isSitewide) || (showWhen === 'false' && !isSitewide);
            
            if (shouldShow) {
                field.style.display = 'block';
                field.classList.remove('hidden');
                field.classList.add('visible');
            } else {
                field.style.display = 'none';
                field.classList.remove('visible');
                field.classList.add('hidden');
            }
        });
    }
}

// Add event listeners for sitewide radio buttons
const sitewideYes = document.getElementById('banner_sitewide_yes');
const sitewideNo = document.getElementById('banner_sitewide_no');

if (sitewideYes) {
    sitewideYes.addEventListener('change', updateConditionalFields);
}
if (sitewideNo) {
    sitewideNo.addEventListener('change', updateConditionalFields);
}
    
    // Initial update
    updateConditionalFields();
    
    // Also run on page load to ensure proper initial state
    document.addEventListener('DOMContentLoaded', function() {
        updateConditionalFields();
    });
    
// HTML sanitization function for preview (similar to frontend)
function sanitizeHtmlForPreview(html) {
    const allowedTags = ['strong', 'em', 'b', 'i', 'span', 'br', 'a'];
    const parser = new DOMParser();
    const doc = parser.parseFromString(html, 'text/html');
    const walker = document.createTreeWalker(doc.body, NodeFilter.SHOW_ELEMENT);
    const toProcess = [];
    while (walker.nextNode()) {
        toProcess.push(walker.currentNode);
    }
    toProcess.forEach(node => {
        const tag = node.tagName.toLowerCase();
        if (allowedTags.includes(tag)) {
            // Clean attributes
            for (let attr of Array.from(node.attributes)) {
                const attrName = attr.name.toLowerCase();
                if (attrName.startsWith('on') || attr.value.match(/^(javascript|data|vbscript):/i)) {
                    node.removeAttribute(attr.name);
                }
            }
            // For 'a' tags, ensure href is present and safe
            if (tag === 'a' && !node.hasAttribute('href')) {
                node.replaceWith(...node.childNodes);
            }
        } else {
            // Unwrap disallowed tags
            node.replaceWith(...node.childNodes);
        }
    });
    return doc.body.innerHTML.replace(/&amp;nbsp;/g, '&nbsp;');
}

// Add keyframes if not present
if (!document.querySelector('#ca-banner-animation-style')) {
    var style = document.createElement('style');
    style.id = 'ca-banner-animation-style';
    style.textContent = '@keyframes ca-banner-preview-scroll { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }';
    document.head.appendChild(style);
}

// Live Preview Functionality
function updatePreview() {
    console.log('updatePreview called');
    try {
    let message = 'Your banner message will appear here...';
    if (typeof tinymce !== 'undefined' && tinymce.get('banner_message')) {
        message = tinymce.get('banner_message').getContent() || '';
    } else {
        const messageField = document.getElementById('banner_message');
        message = messageField?.value || '';
    }
    
    const sanitizedMessage = sanitizeHtmlForPreview(message);
    
    const backgroundColor = document.getElementById('banner_background_color')?.value || '#729946';
    const textColor = document.getElementById('banner_text_color')?.value || '#000000';
    const fontSize = document.getElementById('banner_font_size')?.value || '16';
    const fontFamily = document.getElementById('banner_font_family')?.value || 'Arial';
    const fontWeight = document.getElementById('banner_font_weight')?.value || '600';
    const repeat = document.getElementById('banner_repeat')?.value || '10';
    const speed = document.getElementById('banner_speed')?.value || '60';
        const borderWidth = document.getElementById('banner_border_width')?.value || '0';
        const borderStyle = document.getElementById('banner_border_style')?.value || 'solid';
        const borderColor = document.getElementById('banner_border_color')?.value || '#000000';
        const verticalPadding = document.querySelector('input[name="banner_plugin_settings[vertical_padding]"]')?.value || '10';
        const linkColor = document.getElementById('banner_link_color')?.value || '#0000ff';
    
    // Button settings
    const buttonEnabled = document.querySelector('input[name="banner_plugin_settings[button_enabled]"]')?.checked || false;
    const buttonText = document.querySelector('input[name="banner_plugin_settings[button_text]"]')?.value || '';
    const buttonLink = document.querySelector('input[name="banner_plugin_settings[button_link]"]')?.value || '';
    const buttonColor = document.querySelector('input[name="banner_plugin_settings[button_color]"]')?.value || '#ce7a31';
    const buttonTextColor = document.querySelector('input[name="banner_plugin_settings[button_text_color]"]')?.value || '#ffffff';
    const buttonBorderWidth = document.querySelector('input[name="banner_plugin_settings[button_border_width]"]')?.value || '0';
    const buttonBorderColor = document.querySelector('input[name="banner_plugin_settings[button_border_color]"]')?.value || '#ce7a31';
    const buttonBorderRadius = document.querySelector('input[name="banner_plugin_settings[button_border_radius]"]')?.value || '4';
    const buttonPadding = document.querySelector('input[name="banner_plugin_settings[button_padding]"]')?.value || '8';
    const buttonFontSize = document.querySelector('input[name="banner_plugin_settings[button_font_size]"]')?.value || '14';
    const buttonFontWeight = document.querySelector('select[name="banner_plugin_settings[button_font_weight]"]')?.value || '600';
        const buttonLockEnabled = document.querySelector('input[name="banner_plugin_settings[button_lock_enabled]"]')?.checked || false;
        const buttonLockPosition = document.querySelector('select[name="banner_plugin_settings[button_lock_position]"]')?.value || 'left';
        const buttonGap = document.querySelector('input[name="banner_plugin_settings[button_gap]"]')?.value || '15';
        const buttonNewWindow = document.querySelector('input[name="banner_plugin_settings[button_new_window]"]')?.checked || false;
        
        // Clear existing content
        const previewContent = document.getElementById('ca-banner-preview-content');
        previewContent.innerHTML = '';
        
        // Create banner structure matching frontend
        var banner = document.createElement("div");
        banner.className = "ca-banner-container";
        banner.setAttribute('data-ca-banner', 'true');
        
        var bannerContent = document.createElement("div");
        bannerContent.className = "ca-banner-content";
        
        var messageContainer = document.createElement("div");
        messageContainer.className = "ca-banner-message";
        
        // Clean message (simplified)
        var cleanMessage = sanitizedMessage.replace(/\s+/g, ' ').trim();
        
        // Handle fixed button and repeats
        var buttonAppended = false;
        var isFixed = buttonLockEnabled;
        var button = null;
        if (isFixed && buttonEnabled && buttonText && buttonLink) {
            button = document.createElement("a");
            button.href = buttonLink;
            if (buttonNewWindow) {
                button.target = '_blank';
            }
            button.className = "ca-banner-button";
            button.textContent = buttonText;
            var buttonStyles = [
                'display: inline-block !important',
                'background-color: ' + buttonColor + ' !important',
                'color: ' + buttonTextColor + ' !important',
                'border: ' + buttonBorderWidth + 'px solid ' + buttonBorderColor + ' !important',
                'border-radius: ' + buttonBorderRadius + 'px !important',
                'padding: ' + buttonPadding + 'px !important',
                'font-size: ' + buttonFontSize + 'px !important',
                'font-weight: ' + buttonFontWeight + ' !important',
                'text-decoration: none !important',
                'white-space: nowrap !important',
                'vertical-align: middle !important',
                'position: relative !important',
                'z-index: 2 !important'
            ];
            if (buttonLockPosition === 'right') {
                buttonStyles.push('margin-right: ' + buttonGap + 'px !important');
                buttonStyles.push('margin-left: 10px !important');
            } else if (buttonLockPosition === 'left') {
                buttonStyles.push('margin-left: ' + buttonGap + 'px !important');
                buttonStyles.push('margin-right: 10px !important');
            }
            button.style.cssText = buttonStyles.join('; ');
            buttonAppended = true;
        }
        
        // Add repeats
        for (var i = 0; i < repeat; i++) {
            var msgSpan = document.createElement("span");
            msgSpan.innerHTML = cleanMessage;
            messageContainer.appendChild(msgSpan);
            
            if (!isFixed && buttonEnabled && buttonText && buttonLink) {
                var repButton = document.createElement("a");
                repButton.href = buttonLink;
                if (buttonNewWindow) {
                    repButton.target = '_blank';
                }
                repButton.className = "ca-banner-button";
                repButton.textContent = buttonText;
                repButton.style.cssText = [
                    'display: inline-block !important',
                    'background-color: ' + buttonColor + ' !important',
                    'color: ' + buttonTextColor + ' !important',
                    'border: ' + buttonBorderWidth + 'px solid ' + buttonBorderColor + ' !important',
                    'border-radius: ' + buttonBorderRadius + 'px !important',
                    'padding: ' + buttonPadding + 'px !important',
                    'font-size: ' + buttonFontSize + 'px !important',
                    'font-weight: ' + buttonFontWeight + ' !important',
                    'text-decoration: none !important',
                    'margin-left: 10px !important',
                    'margin-right: 20px !important',
                    'white-space: nowrap !important',
                    'vertical-align: middle !important',
                    'position: relative !important',
                    'z-index: 2 !important'
                ].join('; ');
                messageContainer.appendChild(repButton);
            }
        }
        
        // Message styles
        messageContainer.style.animation = 'ca-banner-preview-scroll ' + speed + 's linear infinite';
        messageContainer.style.display = 'inline-block';
        messageContainer.style.whiteSpace = 'nowrap';
        messageContainer.style.paddingRight = '20px';
        messageContainer.style.willChange = 'transform';
        messageContainer.style.minWidth = '200px';
        
        bannerContent.appendChild(messageContainer);
        
        if (buttonAppended) {
            if (buttonLockPosition === 'left') {
                bannerContent.insertBefore(button, messageContainer);
            } else {
                bannerContent.appendChild(button);
            }
            bannerContent.style.display = 'flex';
            bannerContent.style.alignItems = 'center';
            messageContainer.style.flex = '1 1 auto';
            messageContainer.style.overflow = 'hidden';
        }
        
        // Banner content styles
        bannerContent.style.display = 'flex';
        bannerContent.style.alignItems = 'center';
        bannerContent.style.width = '100%';
        bannerContent.style.overflow = 'hidden';
        bannerContent.style.whiteSpace = 'nowrap';
        bannerContent.style.margin = '0';
        bannerContent.style.padding = '0';
        
        banner.appendChild(bannerContent);
        
        // Banner styles
        banner.style.position = 'relative';
        banner.style.top = '0';
        banner.style.left = '0';
        banner.style.width = '100%';
        banner.style.backgroundColor = backgroundColor;
        banner.style.color = textColor;
        banner.style.padding = verticalPadding + 'px 10px';
        banner.style.textAlign = 'center';
        banner.style.zIndex = '999999';
        banner.style.overflow = 'hidden';
        banner.style.fontWeight = fontWeight;
        banner.style.fontSize = fontSize + 'px';
        banner.style.fontFamily = '"' + fontFamily + '", sans-serif';
        banner.style.borderRadius = '4px';
        banner.style.whiteSpace = 'nowrap';
        banner.style.display = 'flex';
        banner.style.alignItems = 'center';
        banner.style.minHeight = '40px';
        banner.style.borderTop = borderWidth + 'px ' + borderStyle + ' ' + borderColor;
        banner.style.borderBottom = borderWidth + 'px ' + borderStyle + ' ' + borderColor;
        banner.style.margin = '0';
        banner.style.boxShadow = 'none';
        banner.style.lineHeight = '1.4';
        
        previewContent.appendChild(banner);
        
        // Set link color
        banner.style.setProperty('--link-color', linkColor, 'important');
        let linkStyle = document.createElement('style');
        linkStyle.textContent = '.ca-banner-container a { color: var(--link-color) !important; text-decoration: none !important; }';
        banner.appendChild(linkStyle);

        // Force flex
        setTimeout(function() {
            bannerContent.style.display = 'flex !important';
        }, 50);
    } catch (error) {
        console.error('Preview update error:', error);
    }
}

// Add debounce to updatePreview
const debouncedUpdatePreview = debounce(updatePreview, 300);

// Add event listeners for live preview
const previewInputs = [
    'banner_message',
    'banner_background_color', 
    'banner_text_color',
    'banner_font_size',
    'banner_font_family',
    'banner_font_weight',
    'banner_repeat',
    'banner_speed'
];

// Add button field listeners
const buttonFields = [
    'input[name="banner_plugin_settings[button_enabled]"]',
    'input[name="banner_plugin_settings[button_text]"]',
    'input[name="banner_plugin_settings[button_link]"]',
    'input[name="banner_plugin_settings[button_color]"]',
    'input[name="banner_plugin_settings[button_text_color]"]',
    'input[name="banner_plugin_settings[button_border_width]"]',
    'input[name="banner_plugin_settings[button_border_color]"]',
    'input[name="banner_plugin_settings[button_border_radius]"]',
    'input[name="banner_plugin_settings[button_padding]"]',
    'input[name="banner_plugin_settings[button_font_size]"]',
    'select[name="banner_plugin_settings[button_font_weight]"]',
    'input[name="banner_plugin_settings[button_lock_enabled]"]',
    'select[name="banner_plugin_settings[button_lock_position]"]',
    'input[name="banner_plugin_settings[button_gap]"]',
    'input[name="banner_plugin_settings[vertical_padding]"]',
    'input[name="banner_plugin_settings[button_new_window]"]'
];

previewInputs.forEach(function(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.addEventListener('input', debouncedUpdatePreview);
        input.addEventListener('change', debouncedUpdatePreview);
    }
});

// Add TinyMCE event listener for banner message
function setupTinyMCEListeners() {
    console.log('Setting up TinyMCE listeners');
    if (typeof tinymce !== 'undefined') {
        const editor = tinymce.get('banner_message');
        if (editor) {
            editor.on('change', debouncedUpdatePreview);
            editor.on('keyup', debouncedUpdatePreview);
            console.log('CA Banners: TinyMCE listeners added directly');
        } else {
            tinymce.on('AddEditor', function(e) {
                if (e.editor.id === 'banner_message') {
                    e.editor.on('change', debouncedUpdatePreview);
                    e.editor.on('keyup', debouncedUpdatePreview);
                    console.log('CA Banners: TinyMCE listeners added via AddEditor');
                }
            });
            console.log('Waiting for AddEditor event');
        }
    } else {
        console.log('TinyMCE not loaded, retrying...');
        setTimeout(setupTinyMCEListeners, 1000);
    }
}

setupTinyMCEListeners();

// Add event listeners for button fields
buttonFields.forEach(function(selector) {
    const input = document.querySelector(selector);
    if (input) {
        input.addEventListener('input', debouncedUpdatePreview);
        input.addEventListener('change', debouncedUpdatePreview);
    }
});

// Initial preview update with multiple attempts to ensure it works
function initializePreview() {
    updatePreview();
    
    // Try again after a short delay
    setTimeout(function() {
        updatePreview();
    }, 200);
    
    // Also try on window load as fallback
    window.addEventListener('load', function() {
        updatePreview();
    });
}

// Try multiple initialization methods
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initializePreview);
} else {
    initializePreview();
}

// Fallback initialization
setTimeout(initializePreview, 100);

// Add manual trigger for debugging
document.addEventListener('DOMContentLoaded', function() {
    const previewDiv = document.querySelector('.ca-banner-preview');
    if (previewDiv) {
        const triggerButton = document.createElement('button');
        triggerButton.textContent = 'Refresh Preview';
        triggerButton.style.marginTop = '10px';
        triggerButton.onclick = updatePreview;
        previewDiv.appendChild(triggerButton);
    }
});

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Ensure initial update
setTimeout(() => {
    console.log('Initial preview update');
    updatePreview();
}, 1000);

// Image upload functionality
var uploadButton = document.getElementById('upload_image_button');
var imageInput = document.querySelector('input[name="banner_plugin_settings[image]"]');

if (uploadButton) {
    uploadButton.addEventListener('click', function(e) {
        e.preventDefault();
        var image = wp.media({ 
            title: 'Upload Image',
            multiple: false
        }).open()
        .on('select', function(e){
            var uploaded_image = image.state().get('selection').first();
            var image_url = uploaded_image.toJSON().url;
            if (imageInput) {
                imageInput.value = image_url;
            }
        });
    });
}

// Handle toggle switch label updates
function updateToggleLabels() {
    // Enable/Disable Banner toggle
    var enableToggle = document.querySelector('input[name="banner_plugin_settings[enabled]"]');
    var enableToggleLabel = enableToggle ? enableToggle.closest('.ca-banner-toggle-container').querySelector('.ca-banner-toggle-label') : null;
    
    if (enableToggle && enableToggleLabel) {
        enableToggle.addEventListener('change', function() {
            enableToggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    }
    
    // Mobile Display toggle
    var mobileToggle = document.querySelector('input[name="banner_plugin_settings[disable_mobile]"]');
    var mobileToggleLabel = mobileToggle ? mobileToggle.closest('.ca-banner-toggle-container').querySelector('.ca-banner-toggle-label') : null;
    
    if (mobileToggle && mobileToggleLabel) {
        mobileToggle.addEventListener('change', function() {
            mobileToggleLabel.textContent = this.checked ? 'Disabled on Mobile' : 'Enabled on Mobile';
        });
    }
    
    // Button Enable toggle
    var buttonToggle = document.querySelector('input[name="banner_plugin_settings[button_enabled]"]');
    var buttonToggleLabel = buttonToggle ? buttonToggle.closest('.ca-banner-toggle-container').querySelector('.ca-banner-toggle-label') : null;
    
    if (buttonToggle && buttonToggleLabel) {
        buttonToggle.addEventListener('change', function() {
            buttonToggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    }
}
    
    // Initialize toggle label updates
    updateToggleLabels();
    
    // Form validation
    $('#ca-banner-settings-form').on('submit', function(e) {
        var message = $('#banner_message').val().trim();
        var enabled = $('#banner_plugin_settings\\[enabled\\]').is(':checked');
        
        if (enabled && !message) {
            e.preventDefault();
            alert('Please enter a banner message when enabling the banner.');
            $('#banner_message').focus();
            return false;
        }
    });
    
    // Input length validation
    $('#banner_message').on('input', function() {
        var maxLength = 2000;
        var currentLength = $(this).val().length;
        
        if (currentLength > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
            showLengthWarning('Banner message', maxLength);
        }
        
        updateLengthCounter('#banner_message', maxLength);
    });
    
    $('#banner_urls, #banner_exclude_urls').on('input', function() {
        var maxLength = 5000;
        var currentLength = $(this).val().length;
        
        if (currentLength > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
            showLengthWarning('URL list', maxLength);
        }
        
        updateLengthCounter('#' + $(this).attr('id'), maxLength);
    });
    
    $('#banner_button_text').on('input', function() {
        var maxLength = 100;
        var currentLength = $(this).val().length;
        
        if (currentLength > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
            showLengthWarning('Button text', maxLength);
        }
        
        updateLengthCounter('#banner_button_text', maxLength);
    });
    
    $('#banner_button_link').on('input', function() {
        var maxLength = 500;
        var currentLength = $(this).val().length;
        
        if (currentLength > maxLength) {
            $(this).val($(this).val().substring(0, maxLength));
            showLengthWarning('Button link', maxLength);
        }
        
        updateLengthCounter('#banner_button_link', maxLength);
    });
    
    function updateLengthCounter(selector, maxLength) {
        var $field = $(selector);
        var currentLength = $field.val().length;
        var $counter = $field.siblings('.length-counter');
        
        if ($counter.length === 0) {
            $counter = $('<div class="length-counter"></div>');
            $field.after($counter);
        }
        
        $counter.text(currentLength + '/' + maxLength + ' characters');
        
        if (currentLength > maxLength * 0.9) {
            $counter.addClass('warning');
        } else {
            $counter.removeClass('warning');
        }
    }
    
    function showLengthWarning(fieldName, maxLength) {
        var message = fieldName + ' truncated to ' + maxLength + ' characters to prevent DoS attacks.';
        
        // Show warning message
        var $warning = $('<div class="notice notice-warning is-dismissible"><p>' + message + '</p></div>');
        $('.ca-banner-admin-wrap h1').after($warning);
        
        // Auto-dismiss after 5 seconds
        setTimeout(function() {
            $warning.fadeOut();
        }, 5000);
    }
    
    // Auto-save functionality (optional enhancement)
    var autoSaveTimeout;
    $('.ca-banner-textarea, .ca-banner-input, .ca-banner-select').on('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Could implement auto-save here if desired
            console.log('Auto-save triggered');
        }, 2000);
    });
    
    // Cache management functionality
    $('#clear-settings-cache').on('click', function() {
        clearCache('settings');
    });
    
    $('#clear-banner-cache').on('click', function() {
        clearCache('banner');
    });
    
    $('#clear-all-cache').on('click', function() {
        clearCache('all');
    });
    
    function clearCache(type) {
        var button = $('#' + type.replace('all', 'clear-all') + '-cache');
        var originalText = button.text();
        
        // Disable button and show loading
        button.prop('disabled', true).text('Clearing...');
        
        var data = {
            action: 'ca_banners_clear_' + type + '_cache',
            nonce: ca_banners_admin.nonce
        };
        
        $.post(ajaxurl, data, function(response) {
            if (response.success) {
                showCacheMessage(response.data.message, 'success');
            } else {
                showCacheMessage('Error clearing cache: ' + response.data, 'error');
            }
        }).fail(function() {
            showCacheMessage('Error clearing cache', 'error');
        }).always(function() {
            // Re-enable button
            button.prop('disabled', false).text(originalText);
        });
    }
    
    function showCacheMessage(message, type) {
        let messageDiv = $('#cache-message');
        if (messageDiv.length === 0) {
            messageDiv = $('<div id="cache-message" class="notice" style="margin: 10px 0; padding: 10px;"></div>');
            $('#ca-banner-settings-form').prepend(messageDiv);
        }
        messageDiv.removeClass('notice-success notice-error')
                 .addClass('notice-' + type)
                 .html('<p>' + message + '</p>')
                 .show();
        
        setTimeout(function() {
            messageDiv.fadeOut();
        }, 3000);
    }
});
