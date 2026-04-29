// Простой Event Bus для Vue 3
class EventBus {
    constructor() {
        this.events = {};
    }

    on(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
    }

    off(event, callback) {
        if (!this.events[event]) {
            return;
        }
        if (callback) {
            const index = this.events[event].indexOf(callback);
            if (index > -1) {
                this.events[event].splice(index, 1);
            }
        } else {
            delete this.events[event];
        }
    }

    emit(event, ...args) {
        if (!this.events[event]) {
            return;
        }
        this.events[event].forEach(callback => {
            callback(...args);
        });
    }
}

export default new EventBus();

