const codegen = require('postman-code-generators');
const sdk = require('postman-collection');

const arguments = JSON.parse(process.argv.slice(2));
const options = arguments[3] || {};

if (arguments[0] === 'languages') {
    process.stdout.write(codegen.getLanguageList());
    return;
}

if (arguments[0] === 'options') {
    const language = options.language || 'php';
    const variant = options.variant || 'Request';

    codegen.getOptions(language, variant, function (error, options) {
        if (error) {
            process.stdout.write(error);
        } else {
            process.stdout.write(options);
        }
    });

    return;
}

if (arguments[0] === 'convert') {
    const settings = {
        indentCount: 3,
        indentType: 'Space',
        trimRequestBody: true,
        followRedirect: true,
    };

    const language = options.language || 'nodejs';
    const variant = options.variant || 'Requests';

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
