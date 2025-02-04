# modGPT
A simple POC for auto-creation of SEO-friendly meta descriptions

This is an example Plugin that adds a floating magic wand icon next to the “Summary (introtext)” field label. When clicked, it should:
- grabs your resource’s rendered content, 
- use the ChatGPT API to whip up an SEO-optimized meta description, and 
- dumps the result into the introtext field. 

The ChatGPT API key is neatly tucked away in a system setting so that you don’t hard-code secrets like some amateur.

It has three key parts:

1.	The Plugin Code (PHP): This snippet listens to the manager’s page render event and injects our custom JavaScript/CSS if you’re on a resource create/update page.
2.	The JavaScript (ExtJS) Code: This adds the wand icon next to the introtext field and handles the click event to trigger our AJAX call.
3.	The Processor (PHP): This endpoint (registered as a MODX connector action) loads the resource, retrieves its content, sends it off to ChatGPT via its API, and returns the summary.

## Setup

1. Create a system setting `chatgpt_api_key` and paste your ChatGPT API key from https://platform.openai.com/api-keys
2. Put all the files in this repo (save for the readme and license) in place
3. Copy the contents of the plugin and set it to fire on the `OnManagerPageBeforeRender` event