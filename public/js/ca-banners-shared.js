/**
 * CA Banners Shared JavaScript
 * Contains the exact banner creation logic used by both frontend and admin preview
 */

// CA Banners shared JavaScript loaded

// Debug: Check for banner config after 1 second
setTimeout(function() {
    if (typeof caBannerConfig !== 'undefined') {
        // caBannerConfig loaded
    } else {
            console.log('caBannerConfig not found - banner may be disabled or not configured');
    }
}, 1000);

// HTML sanitization function to prevent XSS (shared between frontend and admin)
function caBannerSanitizeHtml(html) {
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
            // Unwrap disallowed tags by replacing with their children
            node.replaceWith(...node.childNodes);
        }
    });
    // Preserve &nbsp; and other entities
    return doc.body.innerHTML.replace(/&amp;nbsp;/g, '&nbsp;');
}

// Function to update banner speed based on current screen size
var caBannerLastUpdate = 0;
function caBannerUpdateSpeed(element, config) {
    if (!element || !config) return;
    
    // Throttle updates to prevent excessive recalculations (max once per 100ms)
    var now = Date.now();
    if (now - caBannerLastUpdate < 100) {
        return;
    }
    caBannerLastUpdate = now;
    
    var baseSpeed = config.speed || 60;
    var mobileMultiplier = config.mobileSpeedMultiplier || 0.5;
    var tabletMultiplier = config.tabletSpeedMultiplier || 0.75;
    
    // Determine device type and apply appropriate speed multiplier
    var isMobile = window.matchMedia && window.matchMedia("(max-width: 768px)").matches;
    var isTablet = window.matchMedia && window.matchMedia("(min-width: 769px) and (max-width: 1024px)").matches;
    
    var finalSpeed = baseSpeed;
    if (isMobile) {
        finalSpeed = baseSpeed * mobileMultiplier;
    } else if (isTablet) {
        finalSpeed = baseSpeed * tabletMultiplier;
    }
    
    // Apply the new animation speed
    element.style.animation = 'ca-banner-preview-scroll ' + finalSpeed + 's linear infinite';
    
    // Speed updated responsively - throttled to prevent excessive updates
}

// Create banner using EXACT same logic for both frontend and admin preview
function caBannerCreateBanner(config, container) {
    // Creating banner with config
    
    // Check if banner already exists
    if (container.querySelector('.ca-banner-container')) {
        // Banner already exists
        return;
    }

    var banner = document.createElement("div");
    banner.className = "ca-banner-container";
    banner.setAttribute('data-ca-banner', 'true');
    
    var bannerContent = document.createElement("div");
    bannerContent.className = "ca-banner-content";
    
    // Create message container
    var messageContainer = document.createElement("div");
    messageContainer.className = "ca-banner-message";
    
    // Create message safely with HTML support - repeat message for scrolling effect
    var message = config.message || '';
    var repeat = Math.max(1, Math.min(100, config.repeat || 10));
    
    // Clean up message content - remove unwanted div elements
    var cleanMessage = message.replace(/&lt;div[^&gt;]*style="[^"]*left:\s*50%[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
    cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*top:\s*50px[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
    cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*left:\s*50%[^"]*top:\s*50px[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
    cleanMessage = cleanMessage.replace(/&lt;div[^&gt;]*style="[^"]*top:\s*50px[^"]*left:\s*50%[^"]*"[^&gt;]*&gt;&lt;\/div&gt;/gi, '');
    cleanMessage = cleanMessage.replace(/\s+/g, ' ').trim(); // Clean up extra spaces
    
    // Repeat the message and button
    var buttonAppended = false;
    var isFixed = config.buttonLockEnabled;
    if (isFixed && config.buttonEnabled && config.buttonText && config.buttonLink) {
        // Create single button for fixed position
        var button = document.createElement("a");
        button.href = config.buttonLink;
        if (config.buttonNewWindow) {
            button.target = '_blank';
        }
        button.className = "ca-banner-button";
        button.textContent = config.buttonText;
        var buttonStyles = [
            'display: inline-block',
            'background-color: ' + (config.buttonColor || '#ce7a31'),
            'color: ' + (config.buttonTextColor || '#ffffff'),
            'border: ' + (config.buttonBorderWidth || 0) + 'px solid ' + (config.buttonBorderColor || '#ce7a31'),
            'border-radius: ' + (config.buttonBorderRadius || 4) + 'px',
            'padding: ' + (config.buttonPadding || 8) + 'px',
            'font-size: ' + (config.buttonFontSize || 14) + 'px',
            'font-weight: ' + (config.buttonFontWeight || '600'),
            'text-decoration: none',
            'white-space: nowrap',
            'vertical-align: middle',
            'position: relative',
            'z-index: 2'
        ];
        if (config.buttonLockPosition === 'right') {
            buttonStyles.push('margin-left: ' + config.buttonMarginLeft + 'px');
            buttonStyles.push('margin-right: ' + config.buttonMarginRight + 'px');
        } else {
            buttonStyles.push('margin-left: ' + config.buttonMarginLeft + 'px');
            buttonStyles.push('margin-right: ' + config.buttonMarginRight + 'px');
        }
        button.style.cssText = buttonStyles.join('; ');
        buttonAppended = true;
    } 
    for (var i = 0; i < repeat; i++) {
        var msgSpan = document.createElement("span");
        msgSpan.innerHTML = caBannerSanitizeHtml(cleanMessage);
        msgSpan.style.display = 'inline-block';
        messageContainer.appendChild(msgSpan);

        if (!isFixed && config.buttonEnabled && config.buttonText && config.buttonLink) {
            var button = document.createElement("a");
            button.href = config.buttonLink;
            if (config.buttonNewWindow) {
                button.target = '_blank';
            }
            button.className = "ca-banner-button";
            button.textContent = config.buttonText;
            button.style.cssText = [
                'display: inline-block !important',
                'background-color: ' + (config.buttonColor || '#ce7a31') + ' !important',
                'color: ' + (config.buttonTextColor || '#ffffff') + ' !important',
                'border: ' + (config.buttonBorderWidth || 0) + 'px solid ' + (config.buttonBorderColor || '#ce7a31') + ' !important',
                'border-radius: ' + (config.buttonBorderRadius || 4) + 'px !important',
                'padding: ' + (config.buttonPadding || 8) + 'px !important',
                'font-size: ' + (config.buttonFontSize || 14) + 'px !important',
                'font-weight: ' + (config.buttonFontWeight || '600') + ' !important',
                'text-decoration: none !important',
                'margin-left: ' + config.buttonMarginLeft + 'px !important',
                'margin-right: ' + config.buttonMarginRight + 'px !important',
                'white-space: nowrap !important',
                'vertical-align: middle !important'
            ].join('; ');
            messageContainer.appendChild(button);
        } else {
            // Add gap using sum of margins
            var gap = parseInt(config.buttonMarginLeft || 10) + parseInt(config.buttonMarginRight || 10);
            msgSpan.style.marginRight = gap + 'px';
        }
    }
    
    // Apply CSS animation for scrolling effect with responsive speed
    var baseSpeed = config.speed || 60;
    var mobileMultiplier = config.mobileSpeedMultiplier || 0.5;
    var tabletMultiplier = config.tabletSpeedMultiplier || 0.75;
    
    // Determine device type and apply appropriate speed multiplier
    var isMobile = window.matchMedia && window.matchMedia("(max-width: 768px)").matches;
    var isTablet = window.matchMedia && window.matchMedia("(min-width: 769px) and (max-width: 1024px)").matches;
    
    var finalSpeed = baseSpeed;
    if (isMobile) {
        finalSpeed = baseSpeed * mobileMultiplier;
    } else if (isTablet) {
        finalSpeed = baseSpeed * tabletMultiplier;
    }
    
    // Debug logging (remove in production)
    if (typeof console !== 'undefined' && console.log) {
        console.log('CA Banners Speed Debug:', {
            baseSpeed: baseSpeed,
            mobileMultiplier: mobileMultiplier,
            tabletMultiplier: tabletMultiplier,
            windowWidth: window.innerWidth,
            isMobile: isMobile,
            isTablet: isTablet,
            finalSpeed: finalSpeed,
            hasMatchMedia: !!window.matchMedia,
            configKeys: Object.keys(config),
            rawConfig: config
        });
    }
    
    messageContainer.style.animation = 'ca-banner-preview-scroll ' + finalSpeed + 's linear infinite';
    messageContainer.style.display = 'inline-block';
    messageContainer.style.whiteSpace = 'nowrap';
    messageContainer.style.paddingRight = '20px';
    messageContainer.style.willChange = 'transform';
    messageContainer.style.minWidth = '200px';
    messageContainer.style.zIndex = '1 !important';
    messageContainer.style.lineHeight = 'normal';
    
    bannerContent.appendChild(messageContainer);
    if (buttonAppended) {
        if (config.buttonLockPosition === 'left') {
            bannerContent.insertBefore(button, messageContainer);
        } else {
            bannerContent.appendChild(button);
        }
        bannerContent.style.display = 'flex';
        bannerContent.style.alignItems = 'center';
        messageContainer.style.flex = '1 1 auto';
        messageContainer.style.overflow = 'hidden';
    }
    
    // Apply styles to banner content
    bannerContent.style.display = 'flex';
    bannerContent.style.alignItems = 'center';
    bannerContent.style.width = '100%';
    bannerContent.style.overflow = 'hidden';
    bannerContent.style.whiteSpace = 'nowrap';
    bannerContent.style.margin = '0';
    bannerContent.style.padding = '0';

    // Force the style with a slight delay to override any theme interference
    setTimeout(function() {
        bannerContent.style.setProperty('display', 'flex', 'important');
    }, 50);
        
    // Add CSS animation (create once, update duration via style attribute)
    if (!document.querySelector('#ca-banner-animation-style')) {
        var style = document.createElement('style');
        style.id = 'ca-banner-animation-style';
        style.textContent = '@keyframes ca-banner-preview-scroll { 0% { transform: translateX(100%); } 100% { transform: translateX(-100%); } }';
        document.head.appendChild(style);
    }
        
    // Apply responsive animation speed (not hardcoded base speed)
    var baseSpeed = config.speed || 60;
    var mobileMultiplier = config.mobileSpeedMultiplier || 0.5;
    var tabletMultiplier = config.tabletSpeedMultiplier || 0.75;
    
    var isMobile = window.matchMedia && window.matchMedia("(max-width: 768px)").matches;
    var isTablet = window.matchMedia && window.matchMedia("(min-width: 769px) and (max-width: 1024px)").matches;
    
    var finalSpeed = baseSpeed;
    if (isMobile) {
        finalSpeed = baseSpeed * mobileMultiplier;
    } else if (isTablet) {
        finalSpeed = baseSpeed * tabletMultiplier;
    }
    
    messageContainer.style.animation = 'ca-banner-preview-scroll ' + finalSpeed + 's linear infinite';
    // Applied responsive speed calculation
        
    // Add animation class
    bannerContent.classList.add('ca-banner-content');

    banner.appendChild(bannerContent);

    // Apply inline styles for maximum compatibility
    banner.style.cssText = [
        'position: relative !important',
        'top: 0 !important',
        'left: 0 !important',
        'width: 100% !important',
        'background-color: ' + (config.backgroundColor || '#729946') + ' !important',
        'color: ' + (config.textColor || '#000000') + ' !important',
        'padding: ' + (config.verticalPadding || 10) + 'px 10px !important',
        'text-align: center !important',
        'z-index: 999999 !important',
        'overflow: hidden !important',
        'font-weight: ' + (config.fontWeight || '600') + ' !important',
        'font-size: ' + (config.fontSize || 16) + 'px !important',
        'font-family: "' + (config.fontFamily || 'Arial') + '", sans-serif !important',
        'border-radius: 0px !important',
        'white-space: nowrap !important',
        'display: flex !important',
        'align-items: center !important',
        'min-height: 40px !important',
        'border-top: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
        'border-bottom: ' + (config.borderWidth || 0) + 'px ' + (config.borderStyle || 'solid') + ' ' + (config.borderColor || '#000000') + ' !important',
        'margin: 0 !important',
        'box-shadow: none !important'
    ].join('; ');

    // Add link color style
    banner.style.setProperty('--link-color', config.linkColor, 'important');
    var linkStyle = document.createElement('style');
    linkStyle.textContent = '.ca-banner-container a { color: var(--link-color) !important; }';
    banner.appendChild(linkStyle);

    container.appendChild(banner);
}
