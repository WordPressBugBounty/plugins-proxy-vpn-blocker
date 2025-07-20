(function(){
	let currentStep = 0;
	let lastHeight = 0;
	const form = document.getElementById('pvb-setup-form');
	const steps = form.querySelectorAll('.pvb-step');

	function calculateVisibleHeight(element) {
		// Wait for any ongoing CSS transitions to complete
		return new Promise((resolve) => {
			// Small delay to ensure CSS transitions have settled
			setTimeout(() => {
				// Get the natural height by temporarily allowing it to expand
				const originalMaxHeight = element.style.maxHeight;
				const originalHeight = element.style.height;
				
				element.style.maxHeight = 'none';
				element.style.height = 'auto';
				
				// Force reflow
				element.offsetHeight;
				
				// Measure the natural height
				const naturalHeight = element.scrollHeight;
				
				// Restore original constraints
				element.style.maxHeight = originalMaxHeight;
				element.style.height = originalHeight;
				
				// Add buffer and resolve
				resolve(naturalHeight + 20); // Increased buffer
			}, 100); // Wait for transitions to complete
		});
	}

	async function adjustWrapperHeight() {
		const wrapper = document.querySelector('.pvb-steps-wrapper');
		const activeStep = wrapper && wrapper.querySelector('.pvb-step.active');
		if (!wrapper || !activeStep) return;

		// Store current scroll position
		const scrollTop = window.pageYOffset || document.documentElement.scrollTop;

		// Lock the current height to enable smooth transition
		const currentHeight = wrapper.offsetHeight;
		wrapper.style.height = currentHeight + 'px';

		// Force reflow
		wrapper.offsetHeight;

		// Calculate target height and transition to it
		const targetHeight = await calculateVisibleHeight(activeStep);
		
		requestAnimationFrame(() => {
			wrapper.style.height = targetHeight + 'px';
			
			// Restore scroll position after transition starts
			setTimeout(() => {
				window.scrollTo(0, scrollTop);
			}, 50);
		});
	}

	function toggleDetails(button) {
		// More robust way to find the content element
		let content = button.nextElementSibling;
		
		// If nextElementSibling doesn't have the right class, search more broadly
		if (!content || !content.classList.contains('details-content')) {
			// Look for .details-content within the same parent container
			const container = button.closest('.pvb-field-wrapper') || button.closest('.pvb-option') || button.closest('.option-header') || button.parentNode;
			content = container.querySelector('.details-content');
		}
		
		// If still not found, try looking for any .details-content after the button
		if (!content) {
			const allContent = document.querySelectorAll('.details-content');
			const buttonIndex = Array.from(document.querySelectorAll('.details-toggle')).indexOf(button);
			content = allContent[buttonIndex];
		}
		
		if (!content) {
			console.warn('Could not find details content for toggle button:', button);
			return;
		}

		const chevron = button.querySelector('.chevron');
		const buttonText = button.querySelector('span');
		const isOpen = content.classList.contains('open');

		if (isOpen) {
			content.classList.remove('open');
			if (chevron) chevron.classList.remove('rotated');
			if (buttonText) buttonText.textContent = 'View details';
		} else {
			content.classList.add('open');
			if (chevron) chevron.classList.add('rotated');
			if (buttonText) buttonText.textContent = 'Hide details';
		}

		// Wait for the CSS transition to complete, then adjust height
		setTimeout(() => {
			adjustWrapperHeight();
		}, 150); // Wait for details animation to complete
	}

	function validateCurrentStep() {
		const currentStepElement = steps[currentStep];
		const requiredFields = currentStepElement.querySelectorAll('input[required], select[required], textarea[required]');
		let isValid = true;
		let firstInvalidField = null;

		requiredFields.forEach(field => {
			const value = field.value.trim();
			const isFieldValid = value !== '';
			
			if (!isFieldValid) {
				isValid = false;
				if (!firstInvalidField) {
					firstInvalidField = field;
				}
				
				// Add error styling
				field.classList.add('pvb-field-error');
				
				// Find or create error message
				let errorMsg = field.parentNode.querySelector('.pvb-error-message');
				if (!errorMsg) {
					errorMsg = document.createElement('div');
					errorMsg.className = 'pvb-error-message';
					errorMsg.style.color = '#d63638';
					errorMsg.style.fontSize = '14px';
					errorMsg.style.marginTop = '5px';
					field.parentNode.appendChild(errorMsg);
				}
				errorMsg.textContent = 'This field is required';
				errorMsg.style.display = 'block';
			} else {
				// Remove error styling and message
				field.classList.remove('pvb-field-error');
				const errorMsg = field.parentNode.querySelector('.pvb-error-message');
				if (errorMsg) {
					errorMsg.style.display = 'none';
				}
			}
		});

		// Focus first invalid field if validation failed
		if (!isValid && firstInvalidField) {
			firstInvalidField.focus();
		}

		return isValid;
	}

	function clearValidationErrors(stepElement) {
		const errorFields = stepElement.querySelectorAll('.pvb-field-error');
		const errorMessages = stepElement.querySelectorAll('.pvb-error-message');
		
		errorFields.forEach(field => field.classList.remove('pvb-field-error'));
		errorMessages.forEach(msg => msg.style.display = 'none');
	}

	function updateNavigationButtons(step) {
		const prevBtn = form.querySelector('[data-prev]');
		const nextBtn = form.querySelector('[data-next]');
		const skipBtn = form.querySelector('[data-skip]');
		const finishBtn = form.querySelector('[type="submit"]');

		prevBtn.style.display = step === 0 ? 'none' : 'inline-block';
		nextBtn.style.display = step === steps.length - 1 ? 'none' : 'inline-block';
		skipBtn.style.display = step === 0 ? 'inline-block' : 'none';
		finishBtn.style.display = step === steps.length - 1 ? 'inline-block' : 'none';
	}

	function updateProgressBar(index) {
		const fill = document.querySelector('.pvb-progress-bar-fill');
		const percentText = document.querySelector('.pvb-progress-percent');
		const percent = Math.round((index) / (steps.length - 1) * 100);

		fill.style.width = `${percent}%`;
		percentText.textContent = `${percent}% Complete`;
	}

	function updateURL(stepIndex) {
		const url = new URL(window.location);
		url.searchParams.set('step', stepIndex + 1); // Steps are 1-indexed in URL
		window.history.pushState({step: stepIndex}, '', url.toString());
	}

	function getStepFromURL() {
		const urlParams = new URLSearchParams(window.location.search);
		const stepParam = urlParams.get('step');
		if (stepParam) {
			const stepIndex = parseInt(stepParam) - 1; // Convert to 0-indexed
			return Math.max(0, Math.min(stepIndex, steps.length - 1)); // Ensure valid range
		}
		return 0; // Default to first step
	}

	function updateStepClasses(newIndex) {
		const wrapper = document.querySelector('.pvb-steps-wrapper');

		steps.forEach((step, i) => {
			step.classList.remove('active');
			if (i !== newIndex) step.classList.add('hidden');
			step.style.zIndex = i === newIndex ? 2 : 1;
		});

		const nextStep = steps[newIndex];
		nextStep.classList.add('active');
		nextStep.classList.remove('hidden');

		updateProgressBar(newIndex);
		updateNavigationButtons(newIndex);
		updateURL(newIndex); // Add this line

		// Adjust after DOM updates
		setTimeout(() => {
			adjustWrapperHeight();
		}, 50);

		currentStep = newIndex;
	}


	function enableWrapperTransition() {
		const wrapper = document.querySelector('.pvb-steps-wrapper');
		if (wrapper) {
			wrapper.style.transition = 'height 0.4s ease';
			wrapper.style.overflow = 'hidden';
		}
	}

	function initializeSteps() {
		// Initialize currentStep from URL
		currentStep = getStepFromURL();
		
		// Set up initial step display
		steps.forEach((step, i) => {
			step.classList.remove('active');
			if (i !== currentStep) step.classList.add('hidden');
			step.style.zIndex = i === currentStep ? 2 : 1;
		});
		
		const activeStep = steps[currentStep];
		activeStep.classList.add('active');
		activeStep.classList.remove('hidden');
		
		updateProgressBar(currentStep);
		updateNavigationButtons(currentStep);
		
		// Set initial height immediately and synchronously
		const wrapper = document.querySelector('.pvb-steps-wrapper');
		
		if (wrapper && activeStep) {
			// Disable transition
			wrapper.style.transition = 'none';
			wrapper.style.overflow = 'hidden';
			
			// Get the natural height synchronously by temporarily removing constraints
			const originalMaxHeight = activeStep.style.maxHeight;
			const originalHeight = activeStep.style.height;
			
			activeStep.style.maxHeight = 'none';
			activeStep.style.height = 'auto';
			
			// Force reflow and measure
			activeStep.offsetHeight;
			const naturalHeight = activeStep.scrollHeight + 20; // Add buffer
			
			// Restore step constraints
			activeStep.style.maxHeight = originalMaxHeight;
			activeStep.style.height = originalHeight;
			
			// Set wrapper height immediately
			wrapper.style.height = naturalHeight + 'px';
			
			// Re-enable transitions after a brief delay
			setTimeout(() => {
				wrapper.style.transition = 'height 0.4s ease';
			}, 100);
		}
	}

	window.addEventListener('popstate', function(e) {
		if (e.state && typeof e.state.step === 'number') {
			const targetStep = e.state.step;
			if (targetStep >= 0 && targetStep < steps.length) {
				updateStepClasses(targetStep);
			}
		} else {
			// Fallback to URL parameter
			const urlStep = getStepFromURL();
			if (urlStep !== currentStep) {
				updateStepClasses(urlStep);
			}
		}
	});

    function showNotification(message, type = 'success') {
        // Create notification element
        const notification = document.createElement('div');
        notification.className = `pvb-notification pvb-notification-${type}`;
        notification.style.cssText = `
            position: fixed;
            top: 32px;
            right: 20px;
            background: ${type === 'success' ? '#46b450' : '#d63638'};
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            z-index: 999999;
            font-size: 14px;
            max-width: 400px;
        `;
        notification.textContent = message;
        
        document.body.appendChild(notification);
        
        // Remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 5000);
    }

	function collectFormData() {
		const formData = new FormData(form);
		const data = new URLSearchParams();
		
		// Add action and nonce
		data.append('action', 'pvb_complete_setup');
		data.append('nonce', pvb_setup_wizard.nonce);
		
		// Add all form fields
		for (let [key, value] of formData.entries()) {
			data.append(key, value);
		}
		
		// Handle toggle switches that might not be in FormData if unchecked
		const toggleFields = [
			'pvb_proxycheckio_VPN_select_box',
			'pvb_proxycheckio_risk_select_box', 
			'pvb_log_user_ip_select_box',
			'pvb_cache_buster',
		];
		
		toggleFields.forEach(fieldName => {
			const field = form.querySelector(`[name="${fieldName}"]`);
			if (field) {
				// Check if it's a checkbox/toggle that's checked
				if (field.type === 'checkbox' || field.classList.contains('toggle-switch')) {
					if (field.checked || field.classList.contains('active') || field.value === 'on') {
						data.set(fieldName, 'on');
					} else {
						data.set(fieldName, '');
					}
				}
			}
		});
		
		return data;
	}

	function handleSkipSetup() {
		// Show loading state
		const skipBtn = form.querySelector('[data-skip]');
		const originalText = skipBtn.textContent;
		skipBtn.textContent = 'Skipping...';
		skipBtn.disabled = true;
		
		// Prepare data for skip action
		const data = new URLSearchParams();
		data.append('action', 'pvb_skip_setup');
		data.append('nonce', pvb_setup_wizard.nonce);
		
		fetch(pvb_setup_wizard.ajax_url, {
			method: 'POST',
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			}
		})
		.then(response => response.json())
		.then(result => {
			if (result.success) {
				showNotification(result.data.message, 'success');
				// Redirect after brief delay
				setTimeout(() => {
					window.location.href = result.data.redirect_url;
				}, 1000);
			} else {
				showNotification(result.data.message || 'An error occurred while skipping setup.', 'error');
				// Restore button state
				skipBtn.textContent = originalText;
				skipBtn.disabled = false;
			}
		})
		.catch(error => {
			console.error('Skip setup error:', error);
			showNotification('An error occurred while skipping setup.', 'error');
			// Restore button state
			skipBtn.textContent = originalText;
			skipBtn.disabled = false;
		});
	}

	function handleFormSubmission() {
		// Validate final step before submission
		if (!validateCurrentStep()) {
			return;
		}
		
		// Show loading state
		const submitBtn = form.querySelector('[type="submit"]');
		const originalText = submitBtn.textContent;
		submitBtn.textContent = 'Completing Setup...';
		submitBtn.disabled = true;
		
		// Collect all form data
		const data = collectFormData();
		
		fetch(pvb_setup_wizard.ajax_url, {
			method: 'POST',
			body: data,
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			}
		})
		.then(response => response.json())
		.then(result => {
			if (result.success) {
				showNotification(result.data.message, 'success');
				// Redirect after brief delay
				setTimeout(() => {
					window.location.href = result.data.redirect_url;
				}, 1000);
			} else {
				showNotification(result.data.message || 'An error occurred while completing setup.', 'error');
				// Restore button state
				submitBtn.textContent = originalText;
				submitBtn.disabled = false;
			}
		})
		.catch(error => {
			console.error('Setup completion error:', error);
			showNotification('An error occurred while completing setup.', 'error');
			// Restore button state
			submitBtn.textContent = originalText;
			submitBtn.disabled = false;
		});
	}

	// Event listeners
	form.addEventListener('click', function(e) {
		if (e.target.matches('[data-next]')) {
			e.preventDefault();
			
			// Validate current step before proceeding
			if (!validateCurrentStep()) {
				// Validation failed, don't proceed
				return;
			}
			
			if (currentStep < steps.length - 1) {
				// Clear any validation errors from the next step
				clearValidationErrors(steps[currentStep + 1]);
				updateStepClasses(currentStep + 1);
			}
		}
		if (e.target.matches('[data-prev]')) {
			e.preventDefault();
			if (currentStep > 0) {
				// Clear validation errors when going back
				clearValidationErrors(steps[currentStep - 1]);
				updateStepClasses(currentStep - 1);
			}
		}
		if (e.target.matches('[data-skip]')) {
			e.preventDefault();
			handleSkipSetup();
		}
		if (e.target.closest('.details-toggle')) {
			e.preventDefault();
			toggleDetails(e.target.closest('.details-toggle'));
		}
	});

	form.addEventListener('submit', function(e) {
		e.preventDefault();
		handleFormSubmission();
	});

	// Wait for full page render before adjusting height
	window.addEventListener('load', () => {
		requestAnimationFrame(() => {
			setTimeout(() => {
				initializeSteps();
			}, 50);
		});
	});
})();