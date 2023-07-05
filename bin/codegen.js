// get root folder of global node modules
//const root = '/opt/homebrew/lib/node_modules';
//const { execSync } = require('child_process');
//const root = execSync('npm root -g').toString().trim();

const arguments = JSON.parse(process.argv.slice(2));
const root = arguments[0];

const codegen = require(`${root}postman-code-generators`);
const sdk = require(`${root}postman-collection`);

const options = arguments[2] || {};

if (arguments[1] === 'languages') {
    process.stdout.write(JSON.stringify(codegen.getLanguageList()));
    return;
}

if (arguments[1] === 'options') {
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

if (arguments[1] === 'convert') {
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
