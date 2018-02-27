gemini.suite('login', (suite) => {
    suite.setUrl('/')
        .setCaptureElements('#user-login-form')
        .capture('plain');
});
