// Wait for the DOM to be fully loaded before executing the script
document.addEventListener("DOMContentLoaded", function() {
    // Get references to the necessary DOM elements
    var generatePromptBtn = document.getElementById('generatePromptBtn');
    var promptTextarea = document.getElementById('promptTextarea');
    var contentprompt = document.getElementById('content_prompt');
    var keywordInput = document.getElementById('keywordInput');
    var generationMethodSelect = document.getElementById('generationMethod');
    var countrySelect = document.getElementById('countrySelect');
    var promptModal = new bootstrap.Modal(document.getElementById('promptModal'));

    // Add a click event listener to the generate prompt button
    generatePromptBtn.addEventListener('click', function () {
        // Get the keyword input value and trim any whitespace
        var keyword = keywordInput.value.trim();
        // If the keyword is empty, show an alert and stop execution
        if (keyword === '') {
            alert('The keyword field cannot be empty.');
            return;
        }

        // Get the selected values from the dropdowns
        var selectedGenerationMethod = generationMethodSelect.options[generationMethodSelect.selectedIndex].value;
        var selectedCountry = countrySelect.options[countrySelect.selectedIndex].value;
        var selectedSubtitle = document.getElementById('subtitleSelect').options[document.getElementById('subtitleSelect').selectedIndex].text;
        var selectedNarration = document.getElementById('narrationSelect').options[document.getElementById('narrationSelect').selectedIndex].text;
        var selectedLanguage = document.getElementById('languageSelect').options[document.getElementById('languageSelect').selectedIndex].text;

        // Generate the prompt string using the selected values
        var generatedPrompt = `[autowp-promptcode]${keyword},${selectedGenerationMethod},${selectedCountry},${selectedLanguage},${selectedSubtitle},${selectedNarration}[/autowp-promptcode]`;

        // Set the generated prompt in the prompt textarea and content prompt input
        promptTextarea.value = generatedPrompt;
        // Select the text in the prompt textarea
        promptTextarea.select();
        // Copy the selected text to the clipboard
        document.execCommand('copy');
        // Set the generated prompt in the content prompt input
        contentprompt.value = generatedPrompt;

        // Hide the prompt modal
        promptModal.hide();
    });
});