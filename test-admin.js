const puppeteer = require('puppeteer');

async function testAdmin() {
    const browser = await puppeteer.launch({
        headless: false,
        args: ['--ignore-certificate-errors', '--ignore-ssl-errors']
    });
    
    try {
        const page = await browser.newPage();
        
        // Listen for console messages and network failures
        page.on('console', msg => console.log('CONSOLE:', msg.text()));
        page.on('requestfailed', request => {
            console.log('FAILED REQUEST:', request.url(), request.failure().errorText);
        });
        
        console.log('Navigating to admin dashboard...');
        await page.goto('https://actec.shop/marktplatz/admin/dashboard', { 
            waitUntil: 'networkidle2',
            timeout: 30000 
        });
        
        // Take a screenshot
        await page.screenshot({ path: '/Users/andre/Documents/dev/claude/marktplatz-odoo/admin-screenshot.png', fullPage: true });
        console.log('Screenshot saved as admin-screenshot.png');
        
        // Check if CSS is loaded by looking for styled elements
        const hasStyles = await page.evaluate(() => {
            const body = document.body;
            const computedStyle = window.getComputedStyle(body);
            return computedStyle.fontFamily !== 'Times' && computedStyle.fontFamily !== 'serif';
        });
        
        console.log('CSS loaded properly:', hasStyles);
        
        // Check for login form
        const hasLoginForm = await page.$('input[name="login[username]"]') !== null;
        console.log('Login form present:', hasLoginForm);
        
        // Check page title
        const title = await page.title();
        console.log('Page title:', title);
        
        // Get any JavaScript errors
        const errors = await page.evaluate(() => {
            return window.console.errors || [];
        });
        
        if (errors.length > 0) {
            console.log('JavaScript errors:', errors);
        }
        
    } catch (error) {
        console.error('Error testing admin:', error);
    } finally {
        await browser.close();
    }
}

testAdmin().catch(console.error);