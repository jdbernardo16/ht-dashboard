<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Content Management
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Search and Filter Bar -->
                <SearchFilter
                    v-model="filters"
                    :filters="[
                        'search',
                        'status',
                        'content_type',
                        'platform',
                        'date',
                    ]"
                    :status-options="statusOptions"
                    :type-options="typeOptions"
                    :platform-options="platformOptions"
                    @apply="applyFilters"
                    @clear="clearFilters"
                />

                <!-- Data Table -->
                <DataTable
                    :data="posts"
                    :columns="columns"
                    :filters="tableFilters"
                    @create="createPost"
                    @view="viewPost"
                    @edit="editPost"
                    @delete="deletePost"
                >
                    <template #image="{ item }">
                        <div class="flex justify-center">
                            <div
                                v-if="item.image_url"
                                class="w-12 h-12 rounded-lg overflow-hidden border border-gray-200"
                            >
                                <img
                                    :src="item.image_url"
                                    :alt="item.title"
                                    class="w-full h-full object-cover"
                                    @error="handleImageError"
                                />
                            </div>
                            <div
                                v-else
                                class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center"
                            >
                                <svg
                                    class="w-6 h-6 text-gray-400"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"
                                    ></path>
                                </svg>
                            </div>
                        </div>
                    </template>

                    <template #title="{ item }">
                        <div class="font-medium text-gray-900">
                            {{ item.title }}
                        </div>
                    </template>

                    <template #platform="{ item }">
                        <div class="flex flex-wrap gap-1">
                            <span
                                v-for="platform in Array.isArray(item.platform)
                                    ? item.platform
                                    : [item.platform]"
                                :key="platform"
                                class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
                            >
                                {{ formatPlatform(platform) }}
                            </span>
                        </div>
                    </template>

                    <template #content_type="{ item }">
                        <span
                            :class="getTypeClass(item.content_type)"
                            class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                            {{ formatType(item.content_type) }}
                        </span>
                    </template>

                    <template #status="{ item }">
                        <span
                            :class="getStatusClass(item.status)"
                            class="px-2 py-1 text-xs font-medium rounded-full"
                        >
                            {{ formatStatus(item.status) }}
                        </span>
                    </template>

                    <template #scheduled_date="{ item }">
                        {{
                            item.scheduled_date
                                ? formatDate(item.scheduled_date)
                                : "N/A"
                        }}
                    </template>

                    <template #user="{ item }">
                        {{ item.user?.name || "System" }}
                    </template>
                </DataTable>

                <!-- Pagination -->
                <Pagination
                    :links="contentPosts.links"
                    :from="contentPosts.from"
                    :to="contentPosts.to"
                    :total="contentPosts.total"
                    @navigate="handlePageChange"
                />
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <FormModal
            :show="showModal"
            :title="isEdit ? 'Edit Content' : 'Create New Content'"
            :fields="formFields"
            :initial-data="form"
            :loading="loading"
            @close="closeModal"
            @submit="handleSubmit"
            ref="formModal"
        />
    </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import DataTable from "@/Components/DataTable.vue";
import FormModal from "@/Components/FormModal.vue";
import SearchFilter from "@/Components/SearchFilter.vue";
import Pagination from "@/Components/Pagination.vue";
import axios from "axios";
import { createApiHeaders } from "@/Utils/utils";

// Props from controller
const props = defineProps({
    contentPosts: {
        type: Object,
        required: true,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

// Reactive data
const categories = ref([]);
const loading = ref(false);
const showModal = ref(false);
const isEdit = ref(false);
const form = ref({});
const filters = ref({
    search: props.filters.search || "",
    status: props.filters.status || "",
    content_type: props.filters.content_type || "",
    platform: props.filters.platform || "",
    date_from: props.filters.date_from || "",
    date_to: props.filters.date_to || "",
});

// Use props data instead of fetching
const posts = computed(() => props.contentPosts.data || []);

// Table columns
const columns = [
    { key: "id", label: "ID", sortable: true },
    { key: "image", label: "Image", sortable: false },
    { key: "title", label: "Title", sortable: true },
    { key: "platform", label: "Platforms", sortable: true },
    { key: "content_type", label: "Type", sortable: true },
    { key: "content_category", label: "Category", sortable: true },
    { key: "status", label: "Status", sortable: true },
    { key: "scheduled_date", label: "Scheduled", type: "date", sortable: true },
    { key: "user", label: "Author", sortable: true },
];

// Status options
const statusOptions = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Published" },
    { value: "scheduled", label: "Scheduled" },
];

// Type options
const typeOptions = [
    { value: "blog", label: "Blog" },
    { value: "social_media", label: "Social Media" },
    { value: "email", label: "Email" },
    { value: "newsletter", label: "Newsletter" },
];

// Platform options
const platformOptions = [
    { value: "facebook", label: "Facebook" },
    { value: "instagram", label: "Instagram" },
    { value: "twitter", label: "Twitter" },
    { value: "linkedin", label: "LinkedIn" },
    { value: "youtube", label: "YouTube" },
    { value: "tiktok", label: "TikTok" },
    { value: "website", label: "Website" },
    { value: "other", label: "Other" },
];

// Form fields
const formFields = [
    { name: "title", label: "Title", type: "text", required: true },
    {
        name: "content",
        label: "Content",
        type: "textarea",
        required: true,
        rows: 5,
    },
    {
        name: "type",
        label: "Type",
        type: "select",
        required: true,
        options: typeOptions,
    },
    {
        name: "status",
        label: "Status",
        type: "select",
        required: true,
        options: statusOptions,
    },
    {
        name: "category_id",
        label: "Category",
        type: "select",
        required: false,
        options: [],
    },
    {
        name: "scheduled_at",
        label: "Scheduled Date",
        type: "datetime-local",
        required: false,
    },
    {
        name: "tags",
        label: "Tags",
        type: "text",
        required: false,
        placeholder: "Enter tags separated by commas",
    },
    {
        name: "meta_description",
        label: "Meta Description",
        type: "textarea",
        required: false,
        rows: 2,
    },
    {
        name: "seo_keywords",
        label: "SEO Keywords",
        type: "text",
        required: false,
        placeholder: "Enter keywords separated by commas",
    },
    {
        name: "image",
        label: "Upload Image",
        type: "file",
        required: false,
        accept: "image/*",
    },
];

// Table filters
const tableFilters = [
    { value: "draft", label: "Draft" },
    { value: "published", label: "Published" },
    { value: "scheduled", label: "Scheduled" },
];

// Methods
const applyFilters = () => {
    const params = {};
    Object.keys(filters.value).forEach((key) => {
        if (filters.value[key]) params[key] = filters.value[key];
    });

    router.get("/content", params, {
        preserveState: true,
        preserveScroll: true,
        only: ["contentPosts", "filters"],
        onError: (errors) => {
            console.error("Error fetching content:", errors);
        },
    });
};

const fetchCategories = async () => {
    try {
        const response = await axios.get("/api/categories", {
            headers: createApiHeaders(),
            withCredentials: true,
        });
        categories.value = response.data.data;
        // Update form fields with categories
        const categoryField = formFields.find((f) => f.name === "category_id");
        if (categoryField) {
            categoryField.options = categories.value.map((category) => ({
                value: category.id,
                label: category.name,
            }));
        }
    } catch (error) {
        console.error("Error fetching categories:", error);
    }
};

const createPost = () => {
    router.visit("/content/create");
};

const editPost = (post) => {
    router.visit(`/content/${post.id}/edit`);
};

const viewPost = (post) => {
    router.visit(`/content/${post.id}`);
};

const deletePost = async (post) => {
    if (confirm("Are you sure you want to delete this content post?")) {
        try {
            await router.delete(`/content/${post.id}`);
            router.reload({ only: ["contentPosts"] });
        } catch (error) {
            console.error("Error deleting content:", error);
        }
    }
};

const handleSubmit = async (formData) => {
    loading.value = true;

    try {
        // Create FormData for file uploads
        const submitData = new FormData();

        // Add all form fields to FormData
        Object.keys(formData).forEach((key) => {
            if (formData[key] instanceof File) {
                // Handle file uploads
                submitData.append(key, formData[key]);
            } else if (Array.isArray(formData[key])) {
                // Handle arrays
                formData[key].forEach((item, index) => {
                    submitData.append(`${key}[${index}]`, item);
                });
            } else {
                // Handle regular fields
                submitData.append(key, formData[key] || "");
            }
        });

        const url = isEdit.value ? `/content/${form.value.id}` : "/content";
        const method = isEdit.value ? "put" : "post";

        await router[method](url, submitData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
            preserveScroll: true,
            onSuccess: () => {
                closeModal();
                router.reload({ only: ["contentPosts"] });
            },
            onError: (errors) => {
                if (formModal.value) {
                    formModal.value.setErrors(errors);
                }
                console.error("Error saving content:", errors);
            },
        });
    } catch (error) {
        console.error("Error submitting form:", error);
    } finally {
        loading.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
    form.value = {};
    if (formModal.value) {
        formModal.value.resetForm();
    }
};

const clearFilters = () => {
    filters.value = {
        search: "",
        status: "",
        content_type: "",
        platform: "",
        date_from: "",
        date_to: "",
    };
    applyFilters();
};

const handlePageChange = (url) => {
    router.visit(url, {
        preserveState: true,
        preserveScroll: true,
        only: ["contentPosts", "filters"],
    });
};

// Utility functions
const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
};

const formatStatus = (status) => {
    return status.charAt(0).toUpperCase() + status.slice(1).replace("_", " ");
};

const formatPlatform = (platform) => {
    const platformMap = {
        website: "Website",
        facebook: "Facebook",
        instagram: "Instagram",
        twitter: "Twitter",
        linkedin: "LinkedIn",
        tiktok: "TikTok",
        youtube: "YouTube",
        pinterest: "Pinterest",
        email: "Email",
        other: "Other",
    };
    return (
        platformMap[platform] ||
        platform.charAt(0).toUpperCase() + platform.slice(1)
    );
};

const formatType = (type) => {
    return type.charAt(0).toUpperCase() + type.slice(1).replace("_", " ");
};

const getStatusClass = (status) => {
    const classes = {
        draft: "bg-gray-100 text-gray-800",
        published: "bg-green-100 text-green-800",
        scheduled: "bg-blue-100 text-blue-800",
    };
    return classes[status] || "bg-gray-100 text-gray-800";
};

const getTypeClass = (type) => {
    const classes = {
        blog: "bg-purple-100 text-purple-800",
        social_media: "bg-blue-100 text-blue-800",
        email: "bg-green-100 text-green-800",
        newsletter: "bg-orange-100 text-orange-800",
    };
    return classes[type] || "bg-gray-100 text-gray-800";
};

const handleImageError = (event) => {
    event.target.style.display = "none";
    event.target.parentElement.innerHTML = `
        <div class="w-12 h-12 rounded-lg bg-gray-100 border border-gray-200 flex items-center justify-center">
            <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
        </div>
    `;
};

// Lifecycle
onMounted(() => {
    fetchCategories();
});
</script>
