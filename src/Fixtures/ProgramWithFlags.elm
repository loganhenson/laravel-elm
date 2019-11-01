module PROGRAM.Main exposing (..)

import Browser
import Html exposing (text)

type alias Flags = { value: String }
type alias Model = { value: String }

main : Program Flags Model ()
main =
    Browser.element
        { init = (\flags -> (flags, Cmd.none))
        , view = (\model -> text model.value)
        , update = (\msg model -> (model, Cmd.none))
        , subscriptions = \_ -> Sub.none
        }
