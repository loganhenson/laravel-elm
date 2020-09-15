module PAGE.Main exposing (..)

import LaravelElm exposing (page, Errors, decodeErrors, receiveNewProps)
import Html exposing (Html, div, text)
import Html.Attributes exposing (class)
import Json.Decode exposing (Decoder, Error, Value, decodeValue, succeed, dict, list, string, int, float, bool)
import Json.Decode.Pipeline exposing (required)


type alias Props =
    { errors : Errors, loading : Bool }


type alias State =
    {}


type alias Model =
    { props : Props
    , state : State
    }


type Msg
    = NewProps Value
    | NoOp


decodeProps : Decoder Props
decodeProps =
    succeed Props
        |> required "errors" decodeErrors
        |> required "loading" bool


stateFromProps : Props -> State
stateFromProps props =
    {}


main : Program Value (Result Error Model) Msg
main =
    page
        { decodeProps = decodeProps
        , stateFromProps = stateFromProps
        , update = update
        , view = view
        , subscriptions = \_ -> receiveNewProps NewProps
        , onMount = \_ -> Cmd.none
        }


update : Msg -> Model -> ( Model, Cmd Msg )
update msg { props, state } =
    case msg of
        NewProps newProps ->
            ( { props = Result.withDefault props <| decodeValue decodeProps newProps
              , state = state
              }
            , Cmd.none
            )
        NoOp ->
            ( { props = props
              , state = state
              }
            , Cmd.none
            )


view : Model -> Html Msg
view { props, state } =
    div [ class "container mx-auto m-4 p-4" ]
        [ text "Hello, PAGE." ]
