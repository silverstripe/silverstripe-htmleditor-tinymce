import Injector from 'lib/Injector';
import TinyMceHtmlEditorField from 'components/TinyMceHtmlEditorField/TinyMceHtmlEditorField';

export default () => {
  Injector.component.registerMany({
    TinyMceHtmlEditorField,
  });
};
