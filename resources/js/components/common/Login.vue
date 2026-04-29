<template>
    <div class="login-container">
        <div class="login-card">
            <h1>{{ $t('login') }}</h1>
            <form @submit.prevent="handleLogin">
                <div class="form-group">
                    <label for="email">Email</label>
                    <input
                        id="email"
                        v-model="form.email"
                        type="email"
                        required
                        autocomplete="email"
                    />
                </div>
                <div class="form-group">
                    <label for="password">Пароль</label>
                    <input
                        id="password"
                        v-model="form.password"
                        type="password"
                        required
                        autocomplete="current-password"
                    />
                </div>
                <button type="submit" :disabled="loading">
                    {{ loading ? $t('loading') : $t('login') }}
                </button>
            </form>
            
            <!-- Всплывающие уведомления -->
            <ErrorNotification :message="error" @close="error = null" />
        </div>
    </div>
</template>

<script>
import { login } from '../../api';
import { getLastAdminUrlOrDefault } from '../../utils/lastAdminUrl';
import ErrorNotification from './ErrorNotification.vue';

export default {
    name: 'Login',
    components: {
        ErrorNotification,
    },
    data() {
        return {
            form: {
                email: '',
                password: '',
            },
            loading: false,
            error: null,
        };
    },
    methods: {
        async handleLogin() {
            this.loading = true;
            this.error = null;

            try {
                const response = await login(this.form.email, this.form.password);
                
                if (response.user) {
                    // Пользователь уже сохранен в authStore при вызове login()
                    this.$router.push(getLastAdminUrlOrDefault());
                }
            } catch (error) {
                this.error = error.response?.data?.message || this.$t('login_error');
            } finally {
                this.loading = false;
            }
        },
    },
};
</script>

<style scoped>
.login-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background: #f5f5f5;
}

.login-card {
    background: white;
    padding: 2rem;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    width: 100%;
    max-width: 400px;
}

h1 {
    margin-bottom: 1.5rem;
    text-align: center;
}

.form-group {
    margin-bottom: 1rem;
}

label {
    display: block;
    margin-bottom: 0.5rem;
}

input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 1rem;
}

button {
    width: 100%;
    padding: 0.75rem;
    background: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    font-size: 1rem;
    cursor: pointer;
}

button:hover:not(:disabled) {
    background: #0056b3;
}

button:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

</style>

