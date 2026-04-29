<template>
    <div class="stat-card">
        <h3>{{ $t('users') }}</h3>
        <p>{{ data.count }}</p>
    </div>
</template>

<script>
export default {
    name: 'UsersCount',
    props: {
        data: {
            type: Object,
            required: true,
            default: () => ({
                count: 0,
            }),
        },
    },
};
</script>

<style scoped>
.stat-card {
    background: white;
    padding: 1.5rem;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.stat-card h3 {
    margin: 0 0 0.5rem 0;
    color: #666;
    font-size: 0.9rem;
}

.stat-card p {
    margin: 0;
    font-size: 2rem;
    font-weight: bold;
    color: #333;
}
</style>

