import type { Category, Goal, Kit, Product } from '../types'
import { goalKeys, kitKeys, messages, type Locale } from './messages'

export const categoryVi: Record<string, { name: string; description: string }> = {
  books: {
    name: 'Sách',
    description: 'Những tựa sách cho buổi tối yên và sự chú ý sâu hơn.',
  },
  writing: {
    name: 'Viết',
    description: 'Bút, giấy và dụng cụ khiến việc viết tay trở nên có chủ đích.',
  },
  'slow-living': {
    name: 'Sống chậm',
    description: 'Vật dụng mời bạn hiện diện, nghi thức, và những ngày không vội.',
  },
  'craft-diy': {
    name: 'Thủ công & DIY',
    description: 'Bộ kit và vật liệu để làm bằng tay.',
  },
  outdoor: {
    name: 'Ngoài trời',
    description: 'Đồ nhẹ cho đi bộ, cắm trại, và không khí thoáng.',
  },
  desk: {
    name: 'Bàn làm việc',
    description: 'Bạn đồng hành yên tĩnh cho đọc, viết, và tập trung.',
  },
}

export const productVi: Record<string, { name: string; short: string; desc: string }> = {
  'philosophy-of-enough': {
    name: 'Triết lý vừa đủ',
    short: 'Tiểu luận mỏng về sống với ít tiếng ồn hơn.',
    desc: 'Người bạn điềm tĩnh về sự chú ý, tiêu dùng, và chọn một đời sống yên hơn. Lý tưởng để đọc tối mà không cần màn hình.',
  },
  'walking-as-thinking': {
    name: 'Đi bộ như suy nghĩ',
    short: 'Tiểu luận về chuyển động, chú ý, và nơi chốn.',
    desc: 'Những bài viết ngắn khiến bước đi thường ngày thành một thực hành. Bỏ túi áo là vừa.',
  },
  'the-quiet-workshop': {
    name: 'Xưởng yên',
    short: 'Câu chuyện của những người làm bằng tay.',
    desc: 'Chân dung thợ thủ công và những căn phòng nơi họ làm việc chậm rãi, cẩn thận.',
  },
  'letters-without-urgency': {
    name: 'Thư không vội',
    short: 'Một cuốn sách về thư từ và sự kiên nhẫn.',
    desc: 'Vì sao viết thư vẫn quan trọng — và cách bắt đầu lại.',
  },
  'morning-pages-companion': {
    name: 'Bạn đồng hành trang sáng',
    short: 'Gợi ý dịu dàng cho viết buổi sớm.',
    desc: 'Những lời nhắc nhẹ và khoảng trống cho nghi thức viết tay mỗi ngày.',
  },
  'reading-journal': {
    name: 'Nhật ký đọc',
    short: 'Ghi sách, trích dẫn, và ghi chú yên.',
    desc: 'Trang kẻ với các mục mềm cho tựa, suy nghĩ, và câu yêu thích. Bìa vải.',
  },
  'field-notes-notebook': {
    name: 'Sổ Field Notes',
    short: 'Sổ túi cho đi bộ và danh sách.',
    desc: 'Sổ bìa mềm nhỏ gọn, vừa túi áo. Trang chấm.',
  },
  'fountain-pen': {
    name: 'Bút máy',
    short: 'Bút máy mượt cho mỗi ngày.',
    desc: 'Bút ngòi thép cân bằng, thân nhựa ấm. Kèm converter và ống mực.',
  },
  'mechanical-pencil': {
    name: 'Bút chì kim',
    short: 'Bút chì kỹ thuật 0.5mm.',
    desc: 'Thân kim loại cho phác thảo, danh sách, và ghi chú dã ngoại.',
  },
  'travel-journal': {
    name: 'Nhật ký du hành',
    short: 'Sổ bìa cứng cho chuyến đi.',
    desc: 'Giấy kem, ruy băng đánh dấu và chun giữ. Dành cho tàu, công viên, quán cà phê.',
  },
  'linen-bookmark-set': {
    name: 'Bộ đánh dấu linen',
    short: 'Ba chiếc bookmark linen mềm.',
    desc: 'Ba bookmark linen hoàn thiện bằng tay, tông đất dịu.',
  },
  'analog-alarm-clock': {
    name: 'Đồng hồ báo thức analog',
    short: 'Thức dậy không cần điện thoại.',
    desc: 'Đồng hồ analog êm, chuông nhẹ, vỏ gỗ ấm. Cho bên giường yên tĩnh.',
  },
  'stoneware-mug': {
    name: 'Ly gốm stoneware',
    short: 'Ly làm tay cho buổi sáng chậm.',
    desc: 'Ly gốm quay tay, men mờ. Đủ rộng cho một tách sáng.',
  },
  'beeswax-candle': {
    name: 'Nến sáp ong',
    short: 'Nến trụ sáp ong không mùi.',
    desc: 'Nến sáp ong cháy sạch cho buổi tối tắt màn hình.',
  },
  'linen-tea-towel': {
    name: 'Khăn linen',
    short: 'Linen giặt đá dùng hằng ngày.',
    desc: 'Khăn linen mềm cho nghi thức bếp và ngày nấu chậm.',
  },
  'ceramic-incense-holder': {
    name: 'Đế nhang gốm',
    short: 'Khay tối giản cho que nhang.',
    desc: 'Đế gốm nặn tay, dáng trầm và chắc.',
  },
  'handmade-model-kit': {
    name: 'Kit mô hình gỗ',
    short: 'Bộ mô hình gỗ cho một chiều yên.',
    desc: 'Các mảnh gỗ cắt chính xác cho mô hình kiến trúc nhỏ. Không cần keo.',
  },
  sketchbook: {
    name: 'Sổ phác thảo',
    short: 'Sổ giấy dày.',
    desc: 'Sổ A4 giấy 120gsm, hợp chì, mực, và màu nước nhẹ.',
  },
  'watercolor-mini-set': {
    name: 'Bộ màu nước mini',
    short: 'Khay màu nước du lịch và cọ.',
    desc: 'Mười hai tông dịu trong hộp kim loại, kèm cọ túi.',
  },
  'embroidery-starter-kit': {
    name: 'Kit thêu khởi đầu',
    short: 'Khung, chỉ, và mẫu đơn giản.',
    desc: 'Đủ thứ cho buổi tối thêu chậm đầu tiên.',
  },
  'woodcarving-starter-block': {
    name: 'Khối gỗ chạm khởi đầu',
    short: 'Khối basswood kèm hướng dẫn.',
    desc: 'Gỗ basswood mềm và hướng dẫn in cho dạng chạm tay đơn giản.',
  },
  'camping-mug': {
    name: 'Ly cắm trại',
    short: 'Ly men cho đường mòn và hiên nhà.',
    desc: 'Ly enamel cổ điển đựng trà, cà phê, hoặc súp bên lửa.',
  },
  'pocket-compass': {
    name: 'La bàn bỏ túi',
    short: 'La bàn đồng nhỏ.',
    desc: 'La bàn đồng nhỏ cho đi bộ định hướng và dẫn đường analog.',
  },
  'waxed-canvas-tote': {
    name: 'Túi tote canvas sáp',
    short: 'Túi bền cho sách và dụng cụ.',
    desc: 'Túi canvas sáp, quai da — cho chợ, thư viện, và đi bộ ban ngày.',
  },
  'trail-water-bottle': {
    name: 'Bình nước đường mòn',
    short: 'Bình inox, không vị nhựa.',
    desc: 'Bình inox thành đơn, nắp vặn đơn giản. Không màn hình, chỉ nước.',
  },
  'pocket-field-guide': {
    name: 'Cẩm nang dã ngoại',
    short: 'Cây và chim địa phương, có minh họa.',
    desc: 'Cẩm nang minh họa nhỏ để để ý những gì mọc và bay quanh ta.',
  },
  'wooden-book-stand': {
    name: 'Giá sách gỗ',
    short: 'Giá đọc gỗ sồi điều chỉnh được.',
    desc: 'Giá sồi đặc giữ sách nấu ăn, nhật ký, và sách bìa cứng ở góc dễ đọc.',
  },
  'desk-lamp': {
    name: 'Đèn bàn',
    short: 'Đèn làm việc ấm cho đọc.',
    desc: 'Đèn bàn điều chỉnh được, ánh sáng ấm, giảm sáng — dịu hơn ánh xanh.',
  },
  'brass-paperweight': {
    name: 'Chặn giấy đồng',
    short: 'Chặn giấy đồng đặc.',
    desc: 'Khối đồng đúc đơn giản giữ thư và giấy tại chỗ.',
  },
  'ceramic-pen-cup': {
    name: 'Cốc đựng bút gốm',
    short: 'Cốc gốm không men cho dụng cụ.',
    desc: 'Hình trụ yên tĩnh cho bút, chì, và kéo trên bàn dịu.',
  },
  'cork-desk-mat': {
    name: 'Thảm bàn bần',
    short: 'Mặt viết bần tự nhiên.',
    desc: 'Thảm bần mềm bảo vệ bàn và làm êm tiếng viết.',
  },
}

export function localizeCategory(category: Category, locale: Locale): Category {
  if (locale !== 'vi') return category
  const tr = categoryVi[category.slug]
  if (!tr) return category
  return { ...category, name: tr.name, description: tr.description }
}

export function localizeProduct(product: Product, locale: Locale): Product {
  if (locale !== 'vi') {
    return {
      ...product,
      category: product.category ? localizeCategory(product.category, locale) : product.category,
    }
  }
  const tr = productVi[product.slug]
  return {
    ...product,
    name: tr?.name ?? product.name,
    short_description: tr?.short ?? product.short_description,
    description: tr?.desc ?? product.description,
    category: product.category ? localizeCategory(product.category, locale) : product.category,
  }
}

export function localizeKit(kit: Kit, locale: Locale): Kit {
  const keys = kitKeys[kit.slug]
  return {
    ...kit,
    name: keys ? (messages[locale][keys.name] ?? kit.name) : kit.name,
    description: keys ? (messages[locale][keys.desc] ?? kit.description) : kit.description,
    products: kit.products.map((p) => localizeProduct(p, locale)),
  }
}

export function localizeGoal(goal: Goal, locale: Locale): Goal {
  const key = goalKeys[goal.slug]
  return {
    ...goal,
    label: key ? (messages[locale][key] ?? goal.label) : goal.label,
  }
}
