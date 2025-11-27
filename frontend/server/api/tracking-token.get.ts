export default defineEventHandler(async (event) => {
    const config = useRuntimeConfig(event)

    const base = config.apiBaseServer as string
    const tokenPath = config.apiTokenPath as string

    const {token} = await $fetch<{ token: string }>(
        `${base}${tokenPath}`,
        {
            headers: {
                accept: 'application/json',
            },
        }
    )

    return {token}
})