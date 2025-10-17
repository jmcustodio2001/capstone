// Translation Service Provider
const translationService = {
    translations: {},
    translate(key, params = {}) {
        let text = this.translations[key] || key;
        Object.keys(params).forEach(param => {
            text = text.replace(`:${param}`, params[param]);
        });
        return text;
    },
    setTranslations(translations) {
        this.translations = translations;
    }
};

window.translationService = translationService;
