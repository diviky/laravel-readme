// get root folder of global node modules
//const { execSync } = require('child_process');
//const root = execSync('npm root -g').toString().trim();

const arguments = JSON.parse(process.argv.slice(2));
const root = arguments[0];
const command = arguments[1];
const options = arguments[2] || {};

const codegen = require(`${root}postman-code-generators`);
const sdk = require(`${root}postman-collection`);

if (command === 'languages') {
    process.stdout.write(JSON.stringify(codegen.getLanguageList()));
    return;
}

if (command === 'options') {
    const language = options.language || 'cURL';
    const variant = options.variant || 'cURL';

    codegen.getOptions(language, variant, function (error, options) {
        if (error) {
            process.stdout.write(error);
        } else {
            process.stdout.write(options);
        }
    });

    return;
}

if (command === 'convert') {
    const settings = {
        indentCount: 4,
        indentType: 'Space',
        trimRequestBody: true,
        followRedirect: true,
    };

    const language = options.language || 'cURL';
    const variant = options.variant || 'cURL';

    var request = new sdk.Request(options.request || {});

    codegen.convert(language, variant, request, settings, function (error, snippet) {
        if (error) {
            process.stdout.write(error);
        } else {
            process.stdout.write(snippet);
        }
    });

    return;
}
