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
        const isSitewide = sitewideYes ? sitewideYes.checked : false;
        
        conditionalFields.forEach(function(field) {
            const showWhen = field.getAttribute('data-show-when');
            const shouldShow = (showWhen === 'true' && isSitewide) || (showWhen === 'false' && !isSitewide);
            
            if (shouldShow) {
                field.classList.remove('hidden');
                field.classList.add('visible');
            } else {
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
    
    // Live Preview Functionality
    function updatePreview() {
        const message = document.getElementById('banner_message')?.value || 'Your banner message will appear here...';
        const backgroundColor = document.getElementById('banner_background_color')?.value || '#729946';
        const textColor = document.getElementById('banner_text_color')?.value || '#000000';
        const fontSize = document.getElementById('banner_font_size')?.value || '16';
        const fontFamily = document.getElementById('banner_font_family')?.value || 'Arial';
        const fontWeight = document.getElementById('banner_font_weight')?.value || '600';
        const repeat = document.getElementById('banner_repeat')?.value || '10';
        const speed = document.getElementById('banner_speed')?.value || '60';
        
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
        const buttonFontWeight = document.querySelector('input[name="banner_plugin_settings[button_font_weight]"]')?.value || '600';
        
        const previewContent = document.getElementById('ca-banner-preview-content');
        if (previewContent) {
            previewContent.style.backgroundColor = backgroundColor;
            previewContent.style.color = textColor;
            previewContent.style.fontSize = fontSize + 'px';
            previewContent.style.fontFamily = fontFamily;
            previewContent.style.fontWeight = fontWeight;
            
            // Create repeated message for preview
            let repeatedMessage = '';
            for (let i = 0; i < Math.min(parseInt(repeat), 5); i++) { // Limit to 5 repeats for preview
                repeatedMessage += message + ' &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; ';
            }
            
            // Create preview content with message and button
            let previewHTML = '<div class="ca-banner-preview-text" style="animation-duration: ' + speed + 's;">' + repeatedMessage;
            
            // Add button if enabled (inside the scrolling text)
            if (buttonEnabled && buttonText && buttonLink) {
                previewHTML += '<a href="' + buttonLink + '" class="ca-banner-preview-button" style="' +
                    'display: inline-block !important; ' +
                    'background-color: ' + buttonColor + ' !important; ' +
                    'color: ' + buttonTextColor + ' !important; ' +
                    'border: ' + buttonBorderWidth + 'px solid ' + buttonBorderColor + ' !important; ' +
                    'border-radius: ' + buttonBorderRadius + 'px !important; ' +
                    'padding: ' + buttonPadding + 'px !important; ' +
                    'font-size: ' + buttonFontSize + 'px !important; ' +
                    'font-weight: ' + buttonFontWeight + ' !important; ' +
                    'text-decoration: none !important; ' +
                    'margin-left: 20px !important; ' +
                    'white-space: nowrap !important; ' +
                    'vertical-align: middle !important;' +
                    '">' + buttonText + '</a>';
            }
            
            previewHTML += '</div>';
            
            previewContent.innerHTML = previewHTML;
        }
    }
    
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
        'input[name="banner_plugin_settings[button_font_weight]"]'
    ];
    
    previewInputs.forEach(function(inputId) {
        const input = document.getElementById(inputId);
        if (input) {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        }
    });
    
    // Add event listeners for button fields
    buttonFields.forEach(function(selector) {
        const input = document.querySelector(selector);
        if (input) {
            input.addEventListener('input', updatePreview);
            input.addEventListener('change', updatePreview);
        }
    });
    
    // Initial preview update with delay to ensure DOM is ready
    setTimeout(function() {
        updatePreview();
    }, 100);
    
    // Also update on window load as fallback
    window.addEventListener('load', function() {
        updatePreview();
    });
    
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
    var enableToggle = document.querySelector('input[name="banner_plugin_settings[enabled]"]');
    var toggleLabel = document.querySelector('.ca-banner-toggle-label');
    
    if (enableToggle && toggleLabel) {
        enableToggle.addEventListener('change', function() {
            toggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    }
    
    // Handle button toggle switch label updates
    var buttonToggle = document.querySelector('input[name="banner_plugin_settings[button_enabled]"]');
    var buttonToggleLabels = document.querySelectorAll('.ca-banner-toggle-label');
    
    if (buttonToggle && buttonToggleLabels.length > 1) {
        var buttonToggleLabel = buttonToggleLabels[1]; // Second toggle label
        buttonToggle.addEventListener('change', function() {
            buttonToggleLabel.textContent = this.checked ? 'Enabled' : 'Disabled';
        });
    }
    
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
    
    // Auto-save functionality (optional enhancement)
    var autoSaveTimeout;
    $('.ca-banner-textarea, .ca-banner-input, .ca-banner-select').on('change', function() {
        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(function() {
            // Could implement auto-save here if desired
            console.log('Auto-save triggered');
        }, 2000);
    });
});
