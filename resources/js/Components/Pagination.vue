<template>
    <div v-if="links && links.length > 0" class="mt-4">
        <div
            class="flex items-center justify-between border-t border-gray-200 bg-white px-4 py-3 sm:px-6"
        >
            <div class="flex flex-1 justify-between sm:hidden">
                <button
                    @click="navigate(links[0]?.url)"
                    :disabled="!links[0]?.url"
                    class="relative inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Previous
                </button>
                <button
                    @click="navigate(links[links.length - 1]?.url)"
                    :disabled="!links[links.length - 1]?.url"
                    class="relative ml-3 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Next
                </button>
            </div>
            <div
                class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between"
            >
                <div>
                    <p class="text-sm text-gray-700">
                        Showing
                        <span class="font-medium">{{ from }}</span>
                        to
                        <span class="font-medium">{{ to }}</span>
                        of
                        <span class="font-medium">{{ total }}</span>
                        results
                    </p>
                </div>
                <div>
                    <nav
                        class="isolate inline-flex -space-x-px rounded-md shadow-sm"
                        aria-label="Pagination"
                    >
                        <button
                            v-for="(link, index) in links"
                            :key="index"
                            @click="navigate(link.url)"
                            :disabled="!link.url"
                            :class="[
                                'relative inline-flex items-center px-4 py-2 text-sm font-semibold',
                                link.active
                                    ? 'z-10 bg-indigo-600 text-white focus:z-20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600'
                                    : link.url
                                    ? 'text-gray-900 ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus:z-20 focus:outline-offset-0'
                                    : 'text-gray-400 cursor-not-allowed',
                                index === 0 ? 'rounded-l-md' : '',
                                index === links.length - 1
                                    ? 'rounded-r-md'
                                    : '',
                            ]"
                            v-html="link.label"
                        />
                    </nav>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { watch } from "vue";

const props = defineProps({
    links: {
        type: Array,
        default: () => [],
    },
    from: {
        type: Number,
        default: 0,
    },
    to: {
        type: Number,
        default: 0,
    },
    total: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["navigate"]);

const navigate = (url) => {
    if (url) {
        emit("navigate", url);
    }
};
</script>
