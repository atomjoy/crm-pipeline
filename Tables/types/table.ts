// resources/js/types/table.ts
import { Component, VNode } from 'vue';

// Przykładowy interfejs danych użytkownika z Laravela
export interface User {
    id: number;
    name: string;
    email: string;
    is_active: boolean;
    created_at: string;
}

// Odrębna struktura filtrów dla strony użytkowników
export interface UserFilters {
    search?: string;
    sort?: keyof User | 'actions'; // Bezpieczne typowanie kluczy na podstawie modelu User
    direction?: 'asc' | 'desc';
}

// Definicja pojedynczej kolumny (generyczna względet typu danych wiersza T)
export interface TableColumn<T = any> {
    key: string;
    label: string;
    // Flaga określająca, czy kolumnę można sortować
    sortable?: boolean;
    // Funkcja render może zwracać VNode (z h()) lub surowy komponent Vue
    render?: (value: any, row: T) => VNode | Component | string | number;
}

// Interfejs dla paginacji z Laravela (Tylko najpotrzebniejsze dane)
export interface SimplePaginatedData<T> {
    data: T[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
}

// Interfejs dla paginacji z Laravela (Pełna struktura z Laravela)
export interface PaginatedData<T> {
    current_page: number;
    data: T[];
    first_page_url: string;
    from: number | null;
    last_page: number;
    last_page_url: string;
    links: Array<{
        url: string | null;
        label: string;
        active: boolean;
    }>;
    next_page_url: string | null;
    path: string;
    per_page: number;
    prev_page_url: string | null;
    to: number | null;
    total: number;
}
