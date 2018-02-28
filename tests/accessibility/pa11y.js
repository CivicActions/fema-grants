// An example of running Pa11y programmatically, reusing
// existing Puppeteer browsers and pages
'use strict';

const pa11y = require('pa11y');
const puppeteer = require('puppeteer');

runPa11y();

// Async function required for us to use await
async function runPa11y() {
  let browser;
  let pages;
  let log;
  try {

    // Launch our own browser
    browser = await puppeteer.launch({
      executablePath: '/usr/bin/google-chrome',
			// Drone doesn't allow changing user, so we need to disable the sandbox.
			args: ['--no-sandbox']
    });

    // Create a page for the test runs
    // (Pages cannot be used in multiple runs)
    pages = [
      await browser.newPage(),
      await browser.newPage()
    ];

    // Logging
    log = {
      debug: console.log,
      error: console.error,
      info: console.log
    }

    // Test http://example.com/ with our shared browser
    const profile = await pa11y('http://web/', {
      browser: browser,
      page: pages[0],
      log: log,
      actions: [
        'set field #edit-name to Marcos.Fletcher',
        'set field #edit-pass to civicactions',
        'click element #edit-submit',
        'wait for url to not be http://web/',
      ],
    });

    // Test http://example.com/otherpage/ with our shared browser
    const sf425 = await pa11y('http://web/node/add/sf425', {
      browser: browser,
      page: pages[1],
      log: log,
    });

    // Output the raw result objects
    console.log(profile);
    console.log(sf425);

    // Close the browser instance and pages now we're done with it
    for (const page of pages) {
      await page.close();
    }
    await browser.close();

  } catch (error) {
    // Output an error if it occurred
    console.error(error.message);

    // Close the browser instance and pages if theys exist
    if (pages) {
      for (const page of pages) {
        await page.close();
      }
    }
    if (browser) {
      await browser.close();
    }
    if (profile.results.length > 0 || sf425.results.length > 0) {
      // If the test failed exit non-zero.
      await process.exit(1);
    }
  }
}
