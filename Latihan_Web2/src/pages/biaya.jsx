import React from 'react'
import { NavLink } from 'react-router'
import Pengeluaran from '../compones/pengeluaran.jsx'

export default function () {
  return (
    <div>
        <NavLink to="/ListPengeluaran">Lihat Pengeluaran</NavLink>
        <Pengeluaran />
    </div>
  )
}
