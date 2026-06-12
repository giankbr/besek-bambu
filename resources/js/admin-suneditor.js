import SUNEDITOR from 'suneditor';
import 'suneditor/css/editor';
import 'suneditor/css/contents';

const buttonList = [
    ['undo', 'redo'],
    ['formatBlock'],
    ['bold', 'italic', 'underline', 'strike'],
    ['removeFormat'],
    ['list'],
    ['link'],
    ['fullScreen', 'codeView'],
];

window.createRichTextEditor = (element, { initialContent = '', onChange } = {}) => {
    const editor = SUNEDITOR.create(element, {
        height: '360',
        minHeight: '200',
        buttonList,
        formats: ['p', 'h2', 'h3', 'h4'],
        defaultTag: 'p',
        resizeEnable: true,
        linkProtocol: 'https://',
        attributesWhitelist: {
            all: 'style',
            a: 'href|target|title|rel',
        },
    });

    editor.setContents(initialContent || '');

    editor.onChange = (contents) => {
        onChange?.(contents);
    };

    return editor;
};
