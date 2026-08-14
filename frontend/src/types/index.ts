export interface User {
  id: number
  name: string
  email: string
  phone?: string | null
  is_admin: boolean
}

export interface Category {
  id: number
  name: string
  slug: string
  description: string
  image_url: string
  products_count?: number
}

export interface Product {
  id: number
  category_id: number
  name: string
  slug: string
  description: string
  short_description: string
  price: string | number
  stock: number
  sku: string
  image_url: string
  is_featured: boolean
  is_active?: boolean
  category?: Category
  average_rating?: number | null
  visible_reviews?: Review[]
}

export interface Review {
  id: number
  user_id: number
  product_id: number
  rating: number
  comment?: string | null
  user?: { id: number; name: string }
  created_at?: string
}

export interface CartItemLocal {
  product_id: number
  quantity: number
  product?: Product
}

export interface Order {
  id: number
  order_number: string
  status: string
  full_name: string
  email: string
  phone: string
  address: string
  city: string
  postal_code: string
  payment_method: string
  subtotal: string | number
  total: string | number
  user_id?: number | null
  user?: { id: number; name: string; email: string } | null
  items?: OrderItem[]
  created_at: string
}

export interface OrderItem {
  id: number
  product_id: number | null
  product_name: string
  product_sku: string
  unit_price: string | number
  quantity: number
  line_total: string | number
}

export interface Kit {
  slug: string
  name: string
  description: string
  total_price: string | number
  products: Product[]
}

export interface Goal {
  slug: string
  label: string
  categories: string[]
}

export interface Paginated<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
