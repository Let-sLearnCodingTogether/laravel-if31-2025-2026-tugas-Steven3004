import { createBrowserRouter } from "react-router";


const router = createBrowserRouter([
  {
    path: "/",
    lazy: {
        Component: async () => {
            const Component = await import("../pages/biaya.jsx")
            return Component.default;
        },
    },
  },
  {
    path: "/ListPengeluaran",
    lazy: {
        Component: async () => {
            const Component = await import("../pages/ListPengeluaran.jsx")
            return Component.default;
        },
    },
  },
]);
export default router;